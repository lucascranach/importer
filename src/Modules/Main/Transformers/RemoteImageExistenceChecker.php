<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Main\Entities\AbstractImagesItem;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

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


    private $serverHost = 'https://lucascranach.org/';
    private $remoteImageBasePath = 'imageserver-2021/%s/%s';
    private $remoteImageDataPath = 'imageserver-2021/%s/imageData-1.1.json';
    private $remoteImageTypeAccessorFunc = null;
    private $cacheDir = null;
    private $cacheFilename = 'remoteImageExistenceChecker';
    private $cacheFileSuffix = '.cache';
    private $cache = [];
    private $objectIdsWithOccuredErrors = [];


    private function __construct()
    {
    }

    public static function withCacheAt(
        string $cacheDir,
        $remoteImageTypeAccessorFunc,
        string $cacheFilename = ''
    ): self {
        $checker = new self;

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
                echo '  Missing remote images for \'' . $id . "' (" . $imageDataURL .")\n";
            }

            $dataToCache = $this->createCacheData($rawImagesData);
            $this->updateCacheFor($id, $dataToCache);
        }

        $cachedItem = $this->getCacheFor($id);
        $cachedImagesForObject = $cachedItem['rawImagesData'];

        if (!is_null($cachedImagesForObject)) {
            $imageType = call_user_func_array(
                $this->remoteImageTypeAccessorFunc,
                [$item, $cachedImagesForObject]
            );

            if ($imageType) {
                try {
                    $preparedImages = $this->prepareRawImages($id, $imageType, $cachedImagesForObject);
                    $item->setImages($preparedImages);
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
        $content = @file_get_contents($url);

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
        string $imageType,
        array $cachedImagesForObject
    ): array {
        $imageTypes = [];

        $imageStack = $cachedImagesForObject['imageStack'];

        if (!isset($imageStack[$imageType])) {
            throw new Error(
                'RemoteImageExistenceChecker: '
                . 'Could not find base stack item ' . $imageType . ' for \'' . $id . '\''
            );
        }
        $baseStackItem = $imageStack[$imageType];

        $imageTypes[$imageType] = $this->getPreparedImageType(
            $baseStackItem,
            $id,
            $imageType,
        );

        return $imageTypes;
    }


    /**
     * @return array[]
     *
     * @psalm-return array{infos: array{maxDimensions: array{width: int, height: int}}, variants: list<mixed>}
     */
    private function getPreparedImageType($stackItem, $id, $imageType): array
    {
        $destinationTypeStructure = [
            'dataJsonSrc' => $this->buildImageDataURL($id),
            'infos' => [
                'maxDimensions' => [ 'width' => 0, 'height' => 0 ],
            ],
            'variants' => [],
        ];

        if (is_null($stackItem['images'])) {
            throw new Error(
                'RemoteImageExistenceChecker: '
                . 'Missing image data for \'' . $id . '\' in base stack item \'' . $imageType . '\''
            );
        }

        $destinationTypeStructure['infos']['maxDimensions'] = [
            'width' => isset($stackItem['maxDimensions']['width']) ? intval($stackItem['maxDimensions']['width']) : 0,
            'height' => isset($stackItem['maxDimensions']['height']) ? intval($stackItem['maxDimensions']['height']) : 0,
        ];

        $images = $stackItem['images'];

        foreach ($images as $image) {
            $destinationTypeStructure['variants'][] = $this->getPreparedImageVariant(
                $image,
                $id,
                $imageType,
            );
        }

        return $destinationTypeStructure;
    }


    private function getPreparedImageVariant($image, $id, $imageType)
    {
        $variantSizes = [];

        foreach ($image as $sizeName => $size) {
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
}
