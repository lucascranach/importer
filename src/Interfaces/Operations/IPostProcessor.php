<?php

namespace CranachDigitalArchive\Importer\Interfaces\Operations;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;


/**
 * Describing a simple item post processor
 */
interface IOperation {
	/**
	 * Handle a passed item
	 *
	 * @param IBaseItem $item Item to be post processed
	 *
	 * @return IBaseItem post processed item
	 */
	function handleItem(IBaseItem $item): IBaseItem;

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