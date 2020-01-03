<?php

namespace CranachImport\Process;

require_once 'IPipeline.php';
require_once 'entities/IBaseItem.php';
require_once 'exporters/IExporter.php';
require_once 'collectors/ICollector.php';
require_once 'postProcessors/IPostProcessor.php';

use CranachImport\Entities\IBaseItem;
use CranachImport\Exporters\IExporter;
use CranachImport\Collectors\ICollector;
use CranachImport\PostProcessors\IPostProcessor;

/**
 * A simple pipeline
 */
class Pipeline implements IPipeline {

	private $exporters = [];
	private $collectors = [];
	private $postProcessors = [];


	function __construct() {

	}


	function addExporter(IExporter $exporter) {
		$this->exporters[] = $exporter;
	}


	function addExporters(array $exporters) {
		$this->exporters = array_merge($this->exporters, $exporters);
	}


	function addCollector(ICollector $collector) {
		$this->collectors[] = $collector;
	}


	function addCollectors(array $collectors) {
		$this->collectors = array_merge($this->collectors, $collectors);
	}


	function addPostProcessor(IPostProcessor $postProcessor) {
		$this->postProcessors[] = $postProcessor;
	}


	function addPostProcessors(array $postProcessors) {
		$this->postProcessors = array_merge($this->postProcessors, $postProcessors);
	}


	function handleIncomingItem(IBaseItem $item) {
		forEach ($this->postProcessors as $postProcessor) {
			$item = $postProcessor->postProcessItem($item);
		}

		foreach ($this->collectors as $collector) {
			$collector->addItem($item);
		}

		foreach ($this->exporters as $exporter) {
			$exporter->pushItem($item);
		}
	}


	function handleDone() {
		foreach ($this->exporters as $exporter) {
			$exporter->done();
		}

		foreach ($this->collectors as $collector) {
			$collector->done();
		}

		foreach ($this->postProcessors as $postProcessor) {
			$postProcessor->done();
		}
	}

}