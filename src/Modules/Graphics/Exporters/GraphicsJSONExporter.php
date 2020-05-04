<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Exporters;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;


/**
 * Graphics exporter on a json flat file base
 */
class GraphicsJSONExporter implements IFileExporter {

	private $destFilepath = null;
	private $items = [];
	private $done = false;


	function __construct(string $destFilepath) {
		$this->destFilepath = $destFilepath;
	}


	function pushItem(IBaseItem $item) {
		if (!($item instanceof Graphic)) {
			throw new Exception('Pushed item is not of expected class \'Graphic\'');
		}

		if ($this->done) {
			throw new \Error('Can\'t push more items after done() was called!');
		}

		$this->items[] = $item;
	}


	function isDone(): bool {
		return $this->done;
	}


	function done() {
		if (is_null($this->destFilepath)) {
			throw new \Error('No filepath for JSON graphics export set!');
		}

		$data = json_encode(array('items' => $this->items), JSON_PRETTY_PRINT);
		$dirname = dirname($this->destFilepath);

		if(!file_exists($dirname)) {
			mkdir($dirname, 0777, TRUE);
		}

		file_put_contents($this->destFilepath, $data);

		$this->done = true;
	}

}