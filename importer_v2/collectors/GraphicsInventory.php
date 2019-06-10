<?php

namespace CranachImport\Collectors;

require_once 'ICollector.php';
require_once 'entities/IBaseItem.php';

use CranachImport\Entities\IBaseItem;


class GraphicsInventory implements ICollector {

	public $inventoryLanguages = [];


	function __construct() {

	}


	function addItem(IBaseItem $item) {
		$langCode = $item->getLangCode();

		if (!isset($this->inventoryLanguages[$langCode])) {
			$this->inventoryLanguages[$langCode] = [];
		}

		$this->inventoryLanguages[$langCode][] = $item;
	}

	function getItems(): array {
		return $this->inventoryLanguages; // TODO: flatten
	}


	function getItemsForLanguage(string $langCode): array {
		if (!isset($this->inventoryLanguages[$langCode])) {
			throw new \Error('Unknown language code: ' . $langCode);
		}

		return $this->inventoryLanguages[$langCode];
	}


	function getLanguageCodes(): array {
		return array_keys($this->inventoryLanguages);
	}

}