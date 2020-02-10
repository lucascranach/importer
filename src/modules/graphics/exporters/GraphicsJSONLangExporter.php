<?php

namespace CranachImport\Modules\Graphics\Exporters;

require_once 'interfaces/exporters/IFileExporter.php';
require_once 'interfaces/entities/IBaseItem.php';
require_once 'modules/graphics/entities/Graphic.php';

use CranachImport\Interfaces\Exporters\IFileExporter;
use CranachImport\Interfaces\Entities\IBaseItem;
use CranachImport\Modules\Graphics\Entities\Graphic;


/**
 * Graphics exporter on a json flat file base (one file per language)
 */
class GraphicsJSONLangExporter implements IFileExporter {

	private $fileExt = 'json';
	private $filename = null;
	private $dirname = null;
	private $destFilepaths = [];
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
		if (!($item instanceof Graphic)) {
			throw new Exception('Pushed item is not of expected class \'Graphic\'!');
		}

		if ($this->isDone()) {
			throw new \Error('Can\'t push more items after done() was called!');
		}

		if (!isset($this->langBuckets[$item->getLangCode()])) {
			$this->langBuckets[$item->getLangCode()] = [];
		}

		$this->langBuckets[$item->getLangCode()][] = $item;
	}


	function isDone(): bool {
		return $this->done;
	}


	function done() {
		if (is_null($this->dirname) || empty($this->dirname)
	     || is_null($this->filename) || empty($this->filename)) {
			throw new \Error('No filepath for JSON graphics export set!');
		}

		foreach ($this->langBuckets as $langCode => $items) {
			$filename = $this->filename . '.' . $langCode . '.' . $this->fileExt;
			$destFilepath = $this->dirname . DIRECTORY_SEPARATOR . $filename;

			$data = json_encode(array('items' => $items), JSON_PRETTY_PRINT);

			if(!file_exists($this->dirname)) {
				mkdir($this->dirname, 0777, TRUE);
			}

			file_put_contents($destFilepath, $data);
		}

		$this->done = true;
	}

}