<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;


class RemoteImageExistenceChecker extends Hybrid {

	private $serverHost = 'http://lucascranach.org/';
	private $remoteImageBasePath = 'imageserver/G_%s/';
	private $remoteImageDataPath = 'imageserver/G_%s/imageData.json';
	private $remoteImageSubDirectoryName = '01_Overall';
	private $cacheDir = null;
	private $cacheFilename = 'remoteImageExistenceChecker.cache.json';
	private $cacheFilepath = null;
	private $cache = [];


	private function __construct()
	{
	}

	public static function new()
	{
		return new self;
	}

	public static function withCacheAt($cacheDir)
	{
		$checker = self::new();

		if (is_string($cacheDir)) {
			if (!file_exists($cacheDir)) {
				mkdir($cacheDir, 0777, true);
			}
			$checker->cacheDir = $cacheDir;
			$checker->cacheFilepath = trim($checker->cacheDir) . DIRECTORY_SEPARATOR . $checker->cacheFilename;
			$checker->restoreCache();
		}

		return $checker;
	}


	function handleItem($item): bool
	{
		if (!($item instanceof Graphic)) {
			throw new \Exception('Pushed item is not of expected class \'Graphic\'');
		}

		$inventoryNumber = $item->getInventoryNumber();

		/* We want to use the exhibition history inventory number for virtual objects */
		if ($item->getIsVirtual()) {

			if (!empty($item->getExhibitionHistory())) {
				$inventoryNumber = $item->getExhibitionHistory();
			} else {
				echo '  Missing exhibition history for virtual object \'' . $inventoryNumber . "'\n";

				$this->next($item);
				return false;
			}
		}

		/* Fill cache to avoid unnecessary duplicate requests for the same resource */
		if (!isset($this->cache[$inventoryNumber])) {
			$interpolatedRemoteImageDataPath = sprintf(
				$this->remoteImageDataPath,
				$inventoryNumber,
			);
			$url = $this->serverHost . $interpolatedRemoteImageDataPath;

			$result = $this->getRemoteImageDataResource($url);

			if (!is_null($result)) {
				$rawImagesData = $result;
			} else {
				echo '  Missing remote images for \'' . $inventoryNumber . "'\n";
				$rawImagesData = null;
			}

			$this->cache[$inventoryNumber] = [
				'isVirtual' => $item->getIsVirtual(),
				'hasExhibitionHistory' => !empty($item->getExhibitionHistory()),
				'rawImagesData' => $rawImagesData,
			];
		}

		$cachedImagesForObject = $this->cache[$inventoryNumber]['rawImagesData'];

		if (!is_null($cachedImagesForObject)) {
			$preparedImages = $this->prepareRawImages($inventoryNumber, $cachedImagesForObject);
			$item->setImages($preparedImages);
		}

		$this->next($item);
		return true;
	}


	function done(ProducerInterface $producer)
	{
		parent::done($producer);

		$this->storeCache();
	}


	private function getRemoteImageDataResource(string $url): ?array
	{
		$content = @file_get_contents($url);

		if ($content === FALSE) {
			return null;
		}

		$statusHeader = $http_response_header[0];
		
		$splitStatusLine = explode(' ', $statusHeader, 3);
		
		if (count($splitStatusLine) !== 3) {
			throw new \Exception('Could not get status code for request!');
		}
		
		$statusCode = $splitStatusLine[1];
		
		/* @TODO: Check content-type on response */

		return ($statusCode[0] === '2') ? json_decode($content, TRUE) : null;
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
			throw new \Exception('Could not find base stack item ' . $this->remoteImageSubDirectoryName);
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
		if (is_null($this->cacheFilepath)) {
			return;
		}

		$cacheAsJSON = json_encode($this->cache);
		file_put_contents($this->cacheFilepath, $cacheAsJSON);
	}


	private function restoreCache()
	{
		if (is_null($this->cacheFilepath) || !file_exists($this->cacheFilepath)) {
			return;
		}

		$cacheAsJSON = file_get_contents($this->cacheFilepath);

		$this->cache = json_decode($cacheAsJSON, true);
	}

}