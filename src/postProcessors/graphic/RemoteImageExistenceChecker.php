<?php

namespace CranachImport\PostProcessors\Graphic;

require_once 'entities/IBaseItem.php';
require_once 'entities/Graphic.php';
require_once 'postProcessors/IPostProcessor.php';

use CranachImport\Entities\IBaseItem;
use CranachImport\Entities\Graphic;
use CranachImport\PostProcessors\IPostProcessor;


class RemoteImageExistenceChecker implements IPostProcessor {

	private $serverHost = 'http://lucascranach.org/';
	private $sizePaths = [
		'xsmall' => 'imageserver/G_%s/01_Overall/G_%s_Overall-xs.jpg',
		'small' => 'imageserver/G_%s/01_Overall/G_%s_Overall-s.jpg',
		'medium' => 'imageserver/G_%s/01_Overall/G_%s_Overall-m.jpg',
		'large' => 'imageserver/G_%s/01_Overall/G_%s_Overall-l.jpg',
		'xlarge' => 'imageserver/G_%s/01_Overall/G_%s_Overall-xl.jpg',
	];
	private $streamContextOptions = [
		'http' => [
			'method' => 'HEAD',
			'header' => "Accept-language: de\r\n",
			'ignore_errors' => true,
		],
	];
	private $cacheDir = null;
	private $cacheFilename = 'remoteImageExistenceChecker.cache.json';
	private $cacheFilepath = null;
	private $cache = [];
	private $isDone = false;


	function __construct($cacheDir = null) {
		if (is_string($cacheDir)) {
			if (!file_exists($cacheDir)) {
				mkdir($cacheDir, 077, true);
			}
			$this->cacheDir = $cacheDir;
			$this->cacheFilepath = trim($this->cacheDir) . DIRECTORY_SEPARATOR . $this->cacheFilename;
			$this->restoreCache();
		}

		$this->streamContext = stream_context_create($this->streamContextOptions);
	}


	function postProcessItem(IBaseItem $item): IBaseItem {
		if (!($item instanceof Graphic)) {
			throw new \Exception('Pushed item is not of expected class \'Graphic\'');
		}

		$inventoryNumber = $item->getInventoryNumber();

		/* We want to use the exhibition history inventory number for virtual objects */
		if ($item->getIsVirtual()) {

			if (!empty($item->getExhibitionHistory())) {
				$inventoryNumber = $item->getExhibitionHistory();
			} else {
				echo 'Missing exhibition history for virtual object \'' . $inventoryNumber . "\'\n";

				return $item;
			}
		}

		/* Fill cache to avoid unnecessary duplicate requests for the same resource */
		if (!isset($this->cache[$inventoryNumber])) {
			$images = [];

			foreach ($this->sizePaths as $size => $path) {
				$interpolatedPath = sprintf($path, $inventoryNumber, $inventoryNumber);
				$url = $this->serverHost . $interpolatedPath;

				if ($this->checkIfResourceExistsRemote($url)) {
					$images[$size] = $url;
				} else {
					$images[$size] = '';
				}
			};

			$this->cache[$inventoryNumber] = [
				'isVirtual' => $item->getIsVirtual(),
				'hasExhibitionHistory' => !empty($item->getExhibitionHistory()),
				'images' => $images,
			];
		}

		$cachedImagesForObject = $this->cache[$inventoryNumber]['images'];
		$item->setImages($cachedImagesForObject);


		$hasAllImages = $this->checkIfAllImagesFoundAndLogMissing(
			$inventoryNumber,
			$cachedImagesForObject,
		);

		$item->setHasAllImages($hasAllImages);

		return $item;
	}


	function isDone(): bool {
		return $this->isDone;
	}


	function done() {
		$this->isDone = true;

		$this->storeCache();
	}


	private function checkIfResourceExistsRemote($url): bool {
		file_get_contents($url, false, $this->streamContext);

		$statusHeader = $http_response_header[0];

		$splitStatusLine = explode(' ', $statusHeader, 3);

		if (count($splitStatusLine) !== 3) {
			throw new \Exception('Could not get status code for request!');
		}

		$statusCode = $splitStatusLine[1];

		return ($statusCode[0] === '2');
	}


	private function checkIfAllImagesFoundAndLogMissing(string $inventoryNumber, array $images): bool {
		$missingImageSizes = [];

		foreach ($images as $size => $path) {
			if (empty($path)) {
				$missingImageSizes[] = $size;
			}
		}

		if (count($missingImageSizes) > 0) {
			echo "Missing remote images for '" . $inventoryNumber . "'"
				. " in size/s " . implode(', ', $missingImageSizes) . "\n";
			return false;
		} else {
			echo "Found all remote images for '" . $inventoryNumber . "'\n";
			return true;
		}
	}


	private function storeCache() {
		if (is_null($this->cacheFilepath)) {
			return;
		}

		$cacheAsJSON = json_encode($this->cache);
		file_put_contents($this->cacheFilepath, $cacheAsJSON);
	}


	private function restoreCache() {
		if (is_null($this->cacheFilepath) || !file_exists($this->cacheFilepath)) {
			return;
		}

		$cacheAsJSON = file_get_contents($this->cacheFilepath);

		$this->cache = json_decode($cacheAsJSON, true);
	}
}