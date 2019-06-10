<?php

namespace CranachImport\Entities;

require_once 'StructuredDimensionEntry.php';


class StructuredDimensionsEntry {

	public $element = '';
	public $dimensionEntries = [];


	function __construct() {

	}


	function setElement(string $element) {
		$this->element = $element;
	}


	function getElement(): string {
		return $this->element;
	}


	function addDimensionEntry(StructuredDimensionEntry $dimensionEntry) {
		$this->dimensionEntry = $dimensionEntry;
	}


	function getDimennsionEntries(): string {
		return $this->dimensionEntries;
	}

}
