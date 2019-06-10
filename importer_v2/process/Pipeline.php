<?php

namespace CranachImport\Process;

require_once 'IPipeline.php';
require_once 'entities/IBaseItem.php';
require_once 'collectors/ICollector.php';
require_once 'importers/IImporter.php';
require_once 'exporters/IExporter.php';

use CranachImport\Entities\IBaseItem;
use CranachImport\Collectors\ICollector;
use CranachImport\Importers\IImporter;
use CranachImport\Exporters\IExporter;


class Pipeline implements IPipeline {

	private $exporters = [];
	private $collectors = [];


	function __construct() {

	}



	function addExporter(IExporter $exporter) {
		$this->exporters[] = $exporter;
	}


	function addCollector(ICollector $collector) {
		$this->collectors[] = $collector;
	}


	function handleIncomingItem(IBaseItem $item) {
		foreach ($this->exporters as $exporter) {
			$exporter->pushItem($item);
		}

		foreach ($this->collectors as $collector) {
			$collector->addItem($item);
		}
	}

	function handleEOF() {
		foreach ($this->exporters as $exporter) {
			$exporter->done();
		}
	}

}