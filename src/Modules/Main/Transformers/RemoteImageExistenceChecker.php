<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Main\Entities\AbstractImagesItem;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class RemoteImageExistenceChecker extends Hybrid
{
    private $serverHost = 'http://lucascranach.org/';
    private $remoteImageBasePath = 'imageserver/%s/';
    private $remoteImageDataPath = 'imageserver/%s/imageData.json';
    private $remoteImageSubDirectoryName = null;
    private $cacheDir = null;
    private $cacheFilename = 'remoteImageExistenceChecker';
    private $cacheFileSuffix = '.cache';
    private $cache = [];


    private function __construct()
    {
    }

    public static function withCacheAt(
        string $cacheDir,
        string $remoteImageSubDirectoryName,
        string $cacheFilename = ''
    )
    {
        $checker = new self;

        if (!empty($remoteImageSibDirectoryName)) {
            $checker->remoteImageSubDirectoryName = $remoteImageSibDirectoryName;
        }

        if (is_string($cacheDir)) {
            if (!file_exists($cacheDir)) {
                mkdir($cacheDir, 0777, true);
            }

            $checker->cacheDir = $cacheDir;

            if (!empty($remoteImageSubDirectoryName)) {
                $checker->remoteImageSubDirectoryName = $remoteImageSubDirectoryName;
            }

            if (!empty($cacheFilename)) {
                $checker->cacheFilename = $cacheFilename;
            }

            $checker->restoreCache();
        }

        if (is_null($checker->cacheDir)) {
            throw new Error('RemoteImageExistenceChecker: Missing cache directory for');
        }

        if (is_null($checker->remoteImageSubDirectoryName)) {
            throw new Error('RemoteImageExistenceChecker: Missing remote image subdirectory name');
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

        /* Fill cache to avoid unnecessary duplicate requests for the same resource */
        if (is_null($this->getCacheFor($id))) {
            $url = $this->buildURLForInventoryNumber($id);

            $result = $this->getRemoteImageDataResource($url);
            $rawImagesData = null;

            if (!is_null($result)) {
                $rawImagesData = $result;
            } else {
                echo '  Missing remote images for \'' . $id . "'\n";
            }

            $dataToCache = $this->createCacheData($rawImagesData);
            $this->updateCacheFor($id, $dataToCache);
        }

        $cachedItem = $this->getCacheFor($id);
        $cachedImagesForObject = $cachedItem['rawImagesData'];

        if (!is_null($cachedImagesForObject)) {
            $preparedImages = $this->prepareRawImages($id, $cachedImagesForObject);
            $item->setImages($preparedImages);
        }

        $this->next($item);
        return true;
    }


    private function buildURLForInventoryNumber(string $inventoryNumber): string
    {
        $interpolatedRemoteImageDataPath = sprintf(
            $this->remoteImageDataPath,
            $inventoryNumber,
        );

        return $this->serverHost . $interpolatedRemoteImageDataPath;
    }


    public function done(ProducerInterface $producer)
    {
        parent::done($producer);

        $this->storeCache();
        $this->cleanUp();
    }


    private function createCacheData(?array $data)
    {
        return [
            'rawImagesData' => $data,
        ];
    }


    private function updateCacheFor(string $key, $data)
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

        return ($statusCode[0] === '2') ? json_decode($content, true) : null;
    }


    private function prepareRawImages(string $inventoryNumber, array $cachedImagesForObject): array
    {
        $destinationStructure = [
            'infos' => [
                'maxDimensions' => [ 'width' => 0, 'height' => 0 ],
            ],
            'sizes' => [
                'xs' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
                's' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
                'm' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
                'l' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
                'xl' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
            ],
        ];

        $imageStack = $cachedImagesForObject['imageStack'];
        $baseStackItem = $imageStack[$this->remoteImageSubDirectoryName];

        if (!isset($baseStackItem)) {
            throw new Error('Could not find base stack item ' . $this->remoteImageSubDirectoryName);
        }

        $destinationStructure['infos']['maxDimensions'] = [
            'width' => intval($baseStackItem['maxDimensions']['width']),
            'height' => intval($baseStackItem['maxDimensions']['height']),
        ];

        foreach ($baseStackItem['images'] as $size => $image) {
            $dimensions = $image['dimensions'];
            $src = $this->serverHost .
                sprintf($this->remoteImageBasePath, $inventoryNumber) .
                $this->remoteImageSubDirectoryName . '/' . $image['src'];

            $destinationStructure['sizes'][$size] = [
                'dimensions' => [
                    'width' => intval($dimensions['width']),
                    'height' => intval($dimensions['height']),
                ],
                'src' => $src,
            ];
        }

        return $destinationStructure;
    }


    private function storeCache()
    {
        $cacheAsJSON = json_encode($this->cache);
        file_put_contents($this->getCachePath(), $cacheAsJSON);
    }


    private function restoreCache()
    {
        $cacheFilepath = $this->getCachePath();
        if (is_null($cacheFilepath) || !file_exists($cacheFilepath)) {
            return;
        }

        $cacheAsJSON = file_get_contents($cacheFilepath);

        $this->cache = json_decode($cacheAsJSON, true);
    }


    private function cleanUp()
    {
        $this->cache = [];
    }
}
