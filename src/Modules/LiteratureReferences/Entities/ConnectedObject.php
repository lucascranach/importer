<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;


/**
 * Representing an connected object
 */
class ConnectedObject {

	public $inventoryNumber = '';
	public $catalogNumber = '';
	public $pageNumber = '';
	public $figureNumber = '';
	public $remarks = '';


	function __construct() {

	}


	function setInventoryNumber(string $inventoryNumber) {
		$this->inventoryNumber = $inventoryNumber;
	}


	function getInventoryNumber(): string {
		return $this->inventoryNumber;
	}


	function setCatalogNumber(string $catalogNumber) {
		$this->catalogNumber = $catalogNumber;
	}


	function getCatalogNumber(): string {
		return $this->catalogNumber;
	}


	function setPageNumber(string $pageNumber) {
		$this->pageNumber = $pageNumber;
	}


	function getPageNumber(): string {
		return $this->pageNumber;
	}

	function setFigureNumber(string $figureNumber) {
		$this->figureNumber = $figureNumber;
	}


	function getFigureNumber(): string {
		return $this->figureNumber;
	}

	function setRemarks(string $remarks) {
		$this->remarks = $remarks;
	}


	function getRemarks(): string {
		return $this->remarks;
	}
}
