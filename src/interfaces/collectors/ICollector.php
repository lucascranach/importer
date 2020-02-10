<?php

namespace CranachImport\Interfaces\Collectors;

require_once 'interfaces/entities/IBaseItem.php';

use CranachImport\Interfaces\Entities\IBaseItem;


/**
 * Describing a simple item collector
 */
interface ICollector {

	/**
	 * Add an item to the collection
	 *
	 * @param IBaseItem $item Item to be added to the collection
	 */
	function addItem(IBaseItem $item);

	/**
	 * Getting all currently existing items in the collection
	 *
	 * @return IBaseItem[]
	 */
	function getItems(): array;

	/**
	 * Called by the pipeline to signal a finished import
	 */
	function done();

	/**
	 * Check if collection items is full / done
	 *
	 * @return bool
	 */
	function isDone(): bool;

}