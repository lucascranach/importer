<?php

namespace CranachImport\Process;

require_once 'entities/IBaseItem.php';
require_once 'collectors/ICollector.php';
require_once 'importers/IImporter.php';
require_once 'exporters/IExporter.php';

use CranachImport\Entities\IBaseItem;
use CranachImport\Collectors\ICollector;
use CranachImport\Importers\IImporter;
use CranachImport\Exporters\IExporter;


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
	 * Adding a collector instance to pass items to
	 *
	 * @param ICollector $collector Collector instance
	 */
	function addCollector(ICollector $collector);

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