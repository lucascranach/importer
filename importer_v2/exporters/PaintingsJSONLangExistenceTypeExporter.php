<?php

namespace CranachImport\Exporters;

require_once 'IFileExporter.php';
require_once 'entities/IBaseItem.php';
require_once 'entities/Painting.php';

use CranachImport\Entities\IBaseItem;
use CranachImport\Entities\Painting;


/**
 * Paintings exporter on a json flat file base
 * - one file per language
 * - linked works
 */
class PaintingsJSONLangExistenceTypeExporter implements IFileExporter {

	private $fileExt = 'json';
	private $filename = null;
	private $dirname = null;
	private $langBuckets = [];
	private $done = false;


	function __construct(string $destFilepath) {
		$filename = basename($destFilepath);
		$this->dirname = trim(dirname($destFilepath));

		$splitFilename = array_map('trim', explode('.', $filename));

		if (count($splitFilename) === 2 && strlen($splitFilename[1])) {
			$this->fileExt = $splitFilename[1];
		}

		$this->filename = $splitFilename[0];
	}


	function pushItem(IBaseItem $item) {
		if (!($item instanceof Painting)) {
			throw new \Exception('Pushed item is not of expected class \'Painting\'');
		}

		if ($this->isDone()) {
			throw new \Error('Can\'t push more items after done() was called!');
		}

		if (!isset($this->langBuckets[$item->getLangCode()])) {
			$this->langBuckets[$item->getLangCode()] = (object) [
				'items' => [],
			];
		}

		$this->langBuckets[$item->getLangCode()]->items[] = $item;
	}


	function isDone(): bool {
		return $this->done;
	}


	function done() {
		if (is_null($this->dirname) || empty($this->dirname)
	     || is_null($this->filename) || empty($this->filename)) {
			throw new \Error('No filepath for JSON paintings export set!');
		}

		foreach ($this->langBuckets as $langCode => $bucket) {
			$filename = implode('.', [
				$this->filename,
				$langCode,
				$this->fileExt,
			]);
			$destFilepath = $this->dirname . DIRECTORY_SEPARATOR . $filename;

			$data = json_encode(array('items' => $bucket->items), JSON_PRETTY_PRINT);
			file_put_contents($destFilepath, $data);
		}

		$this->done = true;
	}

}