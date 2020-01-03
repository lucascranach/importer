<?php

namespace CranachImport\Process;

require_once 'entities/IBaseItem.php';
require_once 'exporters/IExporter.php';
require_once 'collectors/ICollector.php';
require_once 'postProcessors/IPostProcessor.php';

use CranachImport\Entities\IBaseItem;
use CranachImport\Exporters\IExporter;
use CranachImport\Collectors\ICollector;
use CranachImport\PostProcessors\IPostProcessor;


/**
 * Describing the base pipline
 */
interface IPipeline {

	/**
	 * Adding an exporter instance to pass items to
	 *
	 * @param IExporter $exporter Exporter instance
	 */
	function addExporter(IExporter $exporter);

	/**
	 * Adding multiple exporter instances to pass items to
	 *
	 * @param array[IExporter] $exporters Exporter instances
	 */
	function addExporters(array $exporters);

	/**
	 * Adding a collector instance to pass items to
	 *
	 * @param ICollector $collector Collector instance
	 */
	function addCollector(ICollector $collector);

	/**
	 * Adding multiple collector instances to pass items to
	 *
	 * @param array[ICollector] $collectors Collector instances
	 */
	function addCollectors(array $collectors);

	/**
	 * Adding a post processor instance to pass items to
	 *
	 * @param IPostProcessor $processor Post processor instance
	 */
	function addPostProcessor(IPostProcessor $postProcessor);

	/**
	 * Adding multiple post processor instances to pass items to
	 *
	 * @param array[IPostProcessor] $processors Post processors instances
	 */
	function addPostProcessors(array $postProcessors);

	/**
	 * Handling items passed from importers
	 *
	 * @param IBaseItem $item Item passed from an importer
	 */
	function handleIncomingItem(IBaseItem $item);

	/**
	 * Called by an importer to signal the end of the import
	 */
	function handleDone();

}