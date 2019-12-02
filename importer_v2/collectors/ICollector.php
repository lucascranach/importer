<?php

namespace CranachImport\Collectors;

require_once 'entities/IBaseItem.php';

use CranachImport\Entities\IBaseItem;


/**
 * Describing a simple item collector
 */
interface ICollector {

	/**
	 * Addint an item to the collection
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
	 * Called by the pipeline to signal a finsihed import
	 */
	function done();

}