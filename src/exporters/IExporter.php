<?php

namespace CranachImport\Exporters;

require_once 'entities/IBaseItem.php';

use CranachImport\Entities\IBaseItem;


/**
 * Interface describing a simple exporter
 */
interface IExporter {

	/**
	 * Called by the pipeline with a new item to be handled
	 *
	 * @param IBaseItem $item New item
	 */
	function pushItem(IBaseItem $item);

	/**
	 * Getting the done status
	 *
	 * @return bool Done status
	 */
	function isDone(): bool;

	/**
	 * Called by the pipeline when the import is finished
	 */
	function done();

}