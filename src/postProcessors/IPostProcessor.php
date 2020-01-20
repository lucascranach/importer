<?php

namespace CranachImport\PostProcessors;

require_once 'entities/IBaseItem.php';

use CranachImport\Entities\IBaseItem;


/**
 * Describing a simple item post processor
 */
interface IPostProcessor {
	/**
	 * Post process a passed item
	 *
	 * @param IBaseItem $item Item to be post processed
	 *
	 * @return IBaseItem post processed item
	 */
	function postProcessItem(IBaseItem $item): IBaseItem;

	/**
	 * Getting the done status
	 *
	 * @return bool Done status
	 */
	function isDone(): bool;

	/**
	 * Called by the pipeline when the import job is finished
	 */
	function done();
}