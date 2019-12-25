<?php

namespace CranachImport\Collectors;

require_once 'ICollector.php';
require_once 'entities/IBaseItem.php';
require_once 'entities/Graphic.php';

use CranachImport\Entities\IBaseItem;
use CranachImport\Entities\Graphic;


/**
 * Representing a graphics collector for grouping or aggregation purposes
 */
class GraphicsInventory implements ICollector {

	public $inventoryLanguages = [];


	function __construct() {

	}


	function addItem(IBaseItem $item) {
		if (!($item instanceof Graphic)) {
			throw new Exception('Pushed item is not of expected class \'Graphic\'!');
		}

		$langCode = $item->getLangCode();

		if (!isset($this->inventoryLanguages[$langCode])) {
			$this->inventoryLanguages[$langCode] = [];
		}

		$this->inventoryLanguages[$langCode][] = $item;
	}


	function getItems(): array {
		return $this->inventoryLanguages; // TODO: flatten
	}


	function done() {
		/* Not implemented! */
	}


	/**
	 * Getting all items existing in the collection of a certain language
	 *
	 * @param string $langCode
	 * @return iBaseItem Array of items of a certain language
	 */
	function getItemsForLanguage(string $langCode): array {
		if (!isset($this->inventoryLanguages[$langCode])) {
			throw new \Error('Unknown language code: ' . $langCode);
		}

		return $this->inventoryLanguages[$langCode];
	}


	/**
	 * Get all language codes
	 *
	 * @return string[] Array of language codes
	 */
	function getLanguageCodes(): array {
		return array_keys($this->inventoryLanguages);
	}

}