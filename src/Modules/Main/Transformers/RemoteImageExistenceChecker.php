<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Main\Entities\AbstractImagesItem;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use stdClass;

class RemoteImageExistenceChecker extends Hybrid
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

    const PYRAMID = 'pyramid';

    const ALL_IMAGE_TYPES = 'all-image-types';


    private $serverHost = 'https://lucascranach.org/';
    private $remoteImageBasePath = 'imageserver-2021/%s/%s';
    private $remoteImageDataPath = 'data-proxy/image-data.php?obj=%s';
    private $accessKey = '';
    private $remoteImageTypeAccessorFunc = null;
    private $cacheDir = null;
    private $cacheFilename = 'remoteImageExistenceChecker';
    private $cacheFileSuffix = '.cache';
    private $cache = [];
    private $objectIdsWithOccuredErrors = [];
    private $allowedImageTypes = [];


    private function __construct(string $accessKey)
    {
        $this->accessKey = $accessKey;
        $this->allowedImageTypes = [
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

            self::PYRAMID,
        ];
    }

    public static function withCacheAt(
        string $accessKey,
        string $cacheDir,
        $remoteImageTypeAccessorFunc = null,
        string $cacheFilename = ''
    ): self {
        $checker = new self($accessKey);

        if (is_string($remoteImageTypeAccessorFunc) && !empty($remoteImageTypeAccessorFunc)) {
            $imageType = $remoteImageTypeAccessorFunc;
            $checker->remoteImageTypeAccessorFunc = function () use ($imageType): string {
                return $imageType;
            };
        }

        if (is_callable($remoteImageTypeAccessorFunc)) {
            $checker->remoteImageTypeAccessorFunc = $remoteImageTypeAccessorFunc;
        }

        if (!file_exists($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }

        $checker->cacheDir = $cacheDir;

        if (!empty($cacheFilename)) {
            $checker->cacheFilename = $cacheFilename;
        }

        $checker->restoreCache();

        if (is_null($checker->remoteImageTypeAccessorFunc)) {
            throw new Error('RemoteImageExistenceChecker: Missing remote image type accessor');
        }

        return $checker;
    }


    private function getCachePath(): string
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . $this->cacheFilename . $this->cacheFileSuffix;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof AbstractImagesItem)) {
            throw new Error('Pushed item is not of expected class \'AbstractImagesItem\'');
        }

        $id = $item->getImageId();

        if (empty($id)) {
            echo '  Missing imageId for \'' . $item->getId() . "'\n";
            $this->next($item);
            return false;
        }

        $imageDataURL = $this->buildImageDataURL($id);

        /* We simply skip the object, if the same object (but in a different language) already triggered an error */
        if (in_array($id, $this->objectIdsWithOccuredErrors, true)) {
            $this->next($item);
            return false;
        }

        /* Fill cache to avoid unnecessary duplicate requests for the same resource */
        if (is_null($this->getCacheFor($id))) {
            $result = $this->getRemoteImageDataResource($imageDataURL);
            $rawImagesData = null;

            if (!is_null($result)) {
                $rawImagesData = $result;
            } else {
                $this->echoObjectWithMissingImages($id, $imageDataURL);
            }

            $dataToCache = $this->createCacheData($rawImagesData);
            $this->updateCacheFor($id, $dataToCache);
        }

        $cachedItem = $this->getCacheFor($id);
        $cachedImagesForObject = $cachedItem['rawImagesData'];

        if (!is_null($cachedImagesForObject)) {
            $selectedImageTypes = (array) call_user_func_array(
                $this->remoteImageTypeAccessorFunc,
                [$item, $cachedImagesForObject]
            );

            $containsAllSelection = in_array(self::ALL_IMAGE_TYPES, $selectedImageTypes, true);
            $selectedImageTypes = ($containsAllSelection) ? $this->allowedImageTypes : $selectedImageTypes;

            $selectedImageTypesAreEmpty = empty($selectedImageTypes);
            $selectedImageTypesAreAllSupported = count(array_intersect($selectedImageTypes, $this->allowedImageTypes)) === count($this->allowedImageTypes);

            if (!$selectedImageTypesAreEmpty && $selectedImageTypesAreAllSupported) {
                try {
                    $preparedImages = $this->prepareRawImages($id, $selectedImageTypes, $cachedImagesForObject);

                    if (!empty($preparedImages)) {
                        $item->setImages($preparedImages);
                    } else {
                        $this->echoObjectWithMissingImages($id, $imageDataURL);
                    }
                } catch (Error $e) {
                    /* We need to keep track of the same object but in different languages, to prevent duplicate error outputs */
                    echo $e->getMessage() . ' (' . $imageDataURL . ")\n";
                    $this->objectIdsWithOccuredErrors[] = $id;
                }
            }
        }

        $this->next($item);
        return true;
    }


    private function buildImageDataURL(string $id): string
    {
        $interpolatedRemoteImageDataPath = sprintf(
            $this->remoteImageDataPath,
            $id,
        );

        return $this->serverHost . $interpolatedRemoteImageDataPath;
    }


    public function done(ProducerInterface $producer)
    {
        parent::done($producer);

        $this->storeCache();
        $this->cleanUp();
    }


    /**
     * @return (array|null)[]
     *
     * @psalm-return array{rawImagesData: array|null}
     */
    private function createCacheData(?array $data): array
    {
        return [
            'rawImagesData' => $data,
        ];
    }


    private function updateCacheFor(string $key, $data): void
    {
        $this->cache[$key] = $data;
    }


    private function getCacheFor(string $key, $orElse = null)
    {
        return isset($this->cache[$key]) ? $this->cache[$key]: $orElse;
    }


    private function getRemoteImageDataResource(string $url): ?array
    {
        $opts = [
            'http' => [
                'header' => "X-API-KEY: " . $this->accessKey,
            ]
        ];

        $context = stream_context_create($opts);

        $content = @file_get_contents($url, false, $context);

        if ($content === false) {
            return null;
        }

        $statusHeader = $http_response_header[0];

        $splitStatusLine = explode(' ', $statusHeader, 3);

        if (count($splitStatusLine) !== 3) {
            throw new Error('Could not get status code for request!');
        }

        $statusCode = $splitStatusLine[1];

        /* @TODO: Check content-type on response */

        return (in_array($statusCode[0], ['2', '3'], true)) ? json_decode($content, true) : null;
    }


    private function prepareRawImages(
        string $id,
        array $selectedImageTypes,
        array $cachedImagesForObject
    ): array {
        $mappedImageTypes = [];

        if (!isset($cachedImagesForObject['imageStack'])) {
            throw new Error(
                'RemoteImageExistenceChecker: '
                . 'Could not find stack for \'' . $id . '\''
            );
        }

        $imageStack = $cachedImagesForObject['imageStack'];

        foreach ($imageStack as $imageType => $imageTypeValue) {
            $toBeSkipped = !in_array($imageType, $selectedImageTypes, true);
            $isUnknown = !in_array($imageType, $this->allowedImageTypes, true);

            if ($toBeSkipped || $isUnknown) {
                if ($isUnknown) {
                    echo "    " . $id . ": ImageType '" . $imageType . "' is unknown\n";
                }

                continue;
            }

            if (!isset($imageStack[$imageType])) {
                throw new Error(
                    'RemoteImageExistenceChecker: '
                    . 'Could not find stack item ' . $imageType . ' for \'' . $id . '\''
                );
            }

            $preparedImageType = $this->getPreparedImageType(
                $imageTypeValue,
                $id,
                $imageType,
            );

            if (!empty($preparedImageType['images'])) {
                $cleanImageType = $this->getCleanImageType($imageType);
                $mappedImageTypes[$cleanImageType] = $preparedImageType;
            }
        }

        return $mappedImageTypes;
    }


    private function getCleanImageType($imageType)
    {
        return str_replace(['-'], '_', $imageType);
    }


    /**
     * @return array[]
     */
    private function getPreparedImageType($imageTypeValue, $id, $imageType): array
    {
        $images = $imageTypeValue['images'];

        $destinationTypeStructure = [
            'infos' => [
                'maxDimensions' => [ 'width' => 0, 'height' => 0 ],
            ],
            'images' => [],
        ];

        if (is_null($imageTypeValue['images'])) {
            throw new Error(
                'RemoteImageExistenceChecker: '
                . 'Missing image data for \'' . $id . '\' in base stack item \'' . $imageType . '\''
            );
        }

        $destinationTypeStructure['infos']['maxDimensions'] = [
            'width' => isset($imageTypeValue['maxDimensions']['width']) ? intval($imageTypeValue['maxDimensions']['width']) : 0,
            'height' => isset($imageTypeValue['maxDimensions']['height']) ? intval($imageTypeValue['maxDimensions']['height']) : 0,
        ];

        foreach ($images as $image) {
            $destinationTypeStructure['images'][] = [
                'id' => $this->getImageVariantId($image),
                'metadata' => $this->getPreparedImageMetadata($image),
                'sizes' => $this->getPreparedImageVariant(
                    $image,
                    $id,
                    $imageType,
                )
            ];
        }

        return $destinationTypeStructure;
    }


    private function getImageVariantId(array $image): string
    {
        $variantId = '';

        if (isset($image['tiles'])) {
            $tilesImage = $image['tiles'];
            $basename = basename($tilesImage['src']);

            $variantId = $basename;

            $dotPos = strrpos($variantId, '.');
            if ($dotPos !== false) {
                $variantId = substr($variantId, 0, $dotPos);
            }
        }

        return $variantId;
    }


    private function getPreparedImageMetadata($image)
    {
        if (!isset($image['metadata']) || empty($image['metadata'])) {
            return null;
        }

        $langs = ['de', 'en'];
        $metadata = $image['metadata'];

        return array_reduce($langs, function ($acc, $lang) use ($metadata) {
            $fileType = isset($metadata['file-type-' . $lang]) ? $metadata['file-type-' . $lang] : '';
            $description = isset($metadata['image-description-' . $lang]) ? $metadata['image-description-' . $lang] : '';
            $created = isset($metadata['image-created-' . $lang]) ? $metadata['image-created-' . $lang] : '';
            $date = isset($metadata['image-date-' . $lang]) ? $metadata['image-date-' . $lang] : '';
            $source = isset($metadata['image-source-' . $lang]) ? $metadata['image-source-' . $lang] : '';

            if (
                   !empty($fileType)
                || !empty($description)
                || !empty($created)
                || !empty($date)
                || !empty($source)
            ) {
                $acc[$lang] = [
                'fileType' => $fileType,
                'description' => $description,
                'created' => $created,
                'date' => $date,
                'source' => $source,
                ];
            }

            return $acc;
        }, []);
    }


    private function getPreparedImageVariant($image, $id, $imageType)
    {
        $variantSizes = [];

        foreach ($image as $sizeName => $size) {
            if ($sizeName === 'metadata') {
                continue;
            }

            $baseVariant = [
                'dimensions' => [
                    'width' => 0,
                    'height' => 0,
                ],
                'src' => '',
                'type' => isset($size['type']) ? $size['type'] : 'plain',
            ];

            if (isset($size['dimensions']) && !empty($size['dimensions'])) {
                $baseVariant['dimensions'] = [
                    'width' => isset($size['dimensions']['width']) ? intval($size['dimensions']['width']) : 0,
                    'height' => isset($size['dimensions']['height']) ? intval($size['dimensions']['height']) : 0,
                ];
            }

            $imageTypePath = isset($size['path']) ? $size['path'] : $imageType;
            $filepath = $imageTypePath . '/' . $size['src'];

            $baseVariant['src'] = $this->serverHost . sprintf($this->remoteImageBasePath, $id, $filepath);

            $variantSizes[$sizeName] = $baseVariant;
        }

        return $variantSizes;
    }


    private function storeCache(): void
    {
        $cacheAsJSON = json_encode($this->cache);
        file_put_contents($this->getCachePath(), $cacheAsJSON);
    }


    /**
     * @return void
     */
    private function restoreCache()
    {
        $cacheFilepath = $this->getCachePath();
        if (!file_exists($cacheFilepath)) {
            return;
        }

        $cacheAsJSON = file_get_contents($cacheFilepath);

        $this->cache = json_decode($cacheAsJSON, true);
    }


    private function cleanUp(): void
    {
        $this->cache = [];
        $this->objectIdsWithOccuredErrors = [];
    }


    private function echoObjectWithMissingImages($id, $imageDataURL)
    {
        echo '  Missing remote images for \'' . $id . "' (" . $imageDataURL .")\n\tCheck via https://lucascranach.org/admin/image-browser/index.html?artefact=" . $id . "\n";
    }
}
