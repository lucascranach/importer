<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Transformers;

use Error;
use CranachDigitalArchive\Importer\Interfaces\Entities\IImagesItem;
use CranachDigitalArchive\Importer\Interfaces\ICache;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

use GuzzleHttp\Client;

class RemoteDocumentExistenceChecker extends Hybrid
{
    const OVERALL = 'overall';
    const REVERSE = 'reverse';
    const IRR = 'irr';
    const X_RADIOGRAPH = 'x-radiograph';
    const UV_LIGHT = 'uv-light';
    const DETAIL = 'detail';
    const PHOTOMICROGRAPH = 'photomicrograph';
    const CONSERVATION = 'conservation';
    const OTHER = 'other';
    const ANALYSIS = 'analysis';
    const RKD = 'rkd';
    const KOE = 'koe';
    const REFLECTED_LIGHT = 'reflected-light';
    const TRANSMITTED_LIGHT = 'transmitted-light';

    const ALL_EXAMINATION_TYPES = 'all-examination-types';

    private $client;

    private $serverHost = 'https://lucascranach.org/';
    private $remoteDocumentBasePath = 'documents/%s/%s';
    private $remoteDocumentDataPath = 'data-proxy/document-data.php?obj=%s';
    private $accessKey = '';
    private $remoteDocumentTypeAccessorFunc = null;
    private $objectIdsWithOccuredErrors = [];
    private $allowedExaminationKindTypes = [];
    private ICache | null $cache = null;


    private function __construct(string $accessKey)
    {
        $this->accessKey = $accessKey;
        $this->allowedExaminationKindTypes = [
            self::OVERALL,
            self::REVERSE,
            self::IRR,
            self::X_RADIOGRAPH,
            self::UV_LIGHT,
            self::DETAIL,
            self::PHOTOMICROGRAPH,
            self::CONSERVATION,
            self::OTHER,
            self::ANALYSIS,
            self::RKD,
            self::KOE,
            self::REFLECTED_LIGHT,
            self::TRANSMITTED_LIGHT,

            self::ALL_EXAMINATION_TYPES,
        ];

        $this->client = new Client();
    }

    public static function new(
        string $accessKey,
        $remoteDocumentTypeAccessorFunc = null,
    ): self {
        $checker = new self($accessKey);

        if (is_callable($remoteDocumentTypeAccessorFunc)) {
            $checker->remoteDocumentTypeAccessorFunc = $remoteDocumentTypeAccessorFunc;
        } else {
            $examinationType = self::ALL_EXAMINATION_TYPES;

            if (is_string($remoteDocumentTypeAccessorFunc) && !empty($remoteDocumentTypeAccessorFunc)) {
                $examinationType = $remoteDocumentTypeAccessorFunc;
            }

            $checker->remoteDocumentTypeAccessorFunc = function () use ($examinationType): string {
                return $examinationType;
            };
        }

        return $checker;
    }


    public function withCache(ICache $cache): self
    {
        $this->cache = $cache;
        return $this;
    }


    public function handleItem($item): bool
    {
        foreach ($item as $subItem) {
            $this->extendSubItem($subItem);
        }

        $this->next($item);
        return true;
    }


    private function extendSubItem($item): void
    {
        if (!($item instanceof IImagesItem)) {
            throw new Error('Item is not an instance of interface \'IImagesItem\'');
        }

        $id = $item->getRemoteId();

        if (empty($id)) {
            echo '  Missing remoteId for \'' . $item->getId() . "'\n";
            return;
        }

        $documentDataURL = $this->buildDocumentDataURL($id);

        /* We simply skip the object, if the same object (but in a different language) already triggered an error */
        if (in_array($id, $this->objectIdsWithOccuredErrors, true)) {
            return;
        }

        /* Fill cache to avoid unnecessary duplicate requests for the same resource */
        if (is_null($this->getCacheFor($id))) {
            $result = $this->getRemoteDocumentDataResource($documentDataURL);
            $rawDocumentsData = null;

            if (!is_null($result)) {
                $rawDocumentsData = $result;
            }

            $dataToCache = $this->createCacheData($rawDocumentsData);
            $this->updateCacheFor($id, $dataToCache);
        }

        $cachedItem = $this->getCacheFor($id);
        $cachedDocumentsForObject = $cachedItem['rawDocumentsData'];

        if (!is_null($cachedDocumentsForObject)) {
            $selectedExaminationTypes = (array) call_user_func_array(
                $this->remoteDocumentTypeAccessorFunc,
                [$item, $cachedDocumentsForObject]
            );

            $containsAllSelection = in_array(self::ALL_EXAMINATION_TYPES, $selectedExaminationTypes, true);
            $selectedExaminationTypes = ($containsAllSelection) ? $this->allowedExaminationKindTypes : $selectedExaminationTypes;

            $selectedExaminationTypesAreEmpty = empty($selectedExaminationTypes);
            $selectedExamonationTypesAreAllSupported = count(array_intersect($selectedExaminationTypes, $this->allowedExaminationKindTypes)) === count($this->allowedExaminationKindTypes);

            if (!$selectedExaminationTypesAreEmpty && $selectedExamonationTypesAreAllSupported) {
                try {
                    $preparedDocuments = $this->prepareRawDocuments($id, $selectedExaminationTypes, $cachedDocumentsForObject);

                    if (!empty($preparedDocuments)) {
                        $item->setDocuments($preparedDocuments);
                    }
                } catch (Error $e) {
                    /* We need to keep track of the same object but in different languages, to prevent duplicate error outputs */
                    echo $e->getMessage() . ' (' . $documentDataURL . ")\n";
                    $this->objectIdsWithOccuredErrors[] = $id;
                }
            }
        }
    }

    private function buildDocumentDataURL(string $id): string
    {
        $interpolatedRemoteDocumentDataPath = sprintf(
            $this->remoteDocumentDataPath,
            $id,
        );

        return $this->serverHost . $interpolatedRemoteDocumentDataPath;
    }


    public function done(IProducer $producer)
    {
        parent::done($producer);

        $this->storeCache();
        $this->cleanUp();
    }


    /**
     * @return (array|null)[]
     *
     * @psalm-return array{rawDocumentsData: array|null}
     */
    private function createCacheData(?array $data): array
    {
        return [
            'rawDocumentsData' => $data,
        ];
    }


    private function updateCacheFor(string $key, $data): void
    {
        if (!is_null($this->cache)) {
            $this->cache->set($key, $data);
        }
    }


    private function getCacheFor(string $key, $orElse = null)
    {
        return !is_null($this->cache)
            ? $this->cache->get($key, $orElse)
            : null;
    }


    private function getRemoteDocumentDataResource(string $url): ?array
    {
        $resp = $this->client->request('GET', $url, [
            'headers' => [
                'X-API-KEY' => $this->accessKey,
            ],
        ]);

        /* @TODO: Check content-type on response */

        return $resp->getReasonPhrase() == 'OK'
            ? json_decode($resp->getBody(), true, 512, JSON_UNESCAPED_UNICODE)
            : null;
    }


    private function prepareRawDocuments(
        string $id,
        array $selectedExaminationTypes,
        array $cachedDocumentsForObject
    ): array {
        $mappedImageTypes = [];

        if (!isset($cachedDocumentsForObject['documentStack'])) {
            throw new Error(
                'RemoteDocumentExistenceChecker: '
                . 'Could not find stack for \'' . $id . '\''
            );
        }

        $documentStack = $cachedDocumentsForObject['documentStack'];

        foreach ($documentStack as $examinationType => $examinationTypeValue) {
            $toBeSkipped = !in_array($examinationType, $selectedExaminationTypes, true);
            $isUnknown = !in_array($examinationType, $this->allowedExaminationKindTypes, true);

            if ($toBeSkipped || $isUnknown) {
                if ($isUnknown) {
                    echo "    " . $id . ": DocumentType '" . $examinationType . "' is unknown\n";
                }

                continue;
            }

            if (!isset($documentStack[$examinationType])) {
                throw new Error(
                    'RemoteDocumentExistenceChecker: '
                    . 'Could not find stack item ' . $examinationType . ' for \'' . $id . '\''
                );
            }

            $preparedImageType = $this->getPreparedExaminationType(
                $examinationTypeValue,
                $id,
                $examinationType,
            );

            if (!empty($preparedImageType)) {
                $cleanImageType = $this->getCleanExaminationType($examinationType);
                $mappedImageTypes[$cleanImageType] = $preparedImageType;
            }
        }

        return $mappedImageTypes;
    }


    private function getCleanExaminationType($examinationType)
    {
        return str_replace(['-'], '_', $examinationType);
    }


    /**
     * @return array[]
     */
    private function getPreparedExaminationType($examinationTypeValue, $id, $examinationType): array
    {
        return array_map(function ($value) use ($id, $examinationType) {
            $imageTypePath = isset($value['path']) ? $value['path'] : $examinationType;
            $filepath = $imageTypePath . '/' . $value['src'];

            return [
                'id' => $this->getDocumentId($value['src']),
                'src' => $this->serverHost . sprintf($this->remoteDocumentBasePath, $id, $filepath),
            ];
        }, $examinationTypeValue);
    }


    private function getDocumentId(string $documentSrc): string
    {
        $documentId = basename($documentSrc);

        $dotPos = strrpos($documentId, '.');
        if ($dotPos !== false) {
            $documentId = substr($documentId, 0, $dotPos);
        }

        return $documentId;
    }


    private function storeCache(): void
    {
        if (!is_null($this->cache)) {
            $this->cache->store();
        }
    }


    private function cleanUp(): void
    {
        $this->objectIdsWithOccuredErrors = [];
    }
}
