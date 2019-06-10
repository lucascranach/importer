<?php

namespace CranachImport\Exporters;

require_once 'IFileExporter.php';
require_once 'entities/IBaseItem.php';

use CranachImport\Entities\IBaseItem;


class GraphicsJSONExporter implements IFileExporter {

	private $destFilepath = null;
	private $items = [];
	private $done = false;


	function __construct(string $destFilepath) {
		$this->destFilepath = $destFilepath;
	}


	function pushItem(IBaseItem $item) {
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
		file_put_contents($this->destFilepath, $data);

		$this->done = true;
	}

}