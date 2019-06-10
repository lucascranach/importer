<?php

namespace CranachImport\Entities;


class CatalogWorkReference {

	public $description = '';
	public $referenceNumber = '';


	function __construct() {

	}


	function setDescription(string $description) {
		$this->description = $description;
	}


	function getDescription(): string {
		return $this->description;
	}


	function setReferenceNumber(string $referenceNumber) {
		$this->referenceNumber = $referenceNumber;
	}


	function getReferenceNumber(): string {
		return $this->referenceNumber;
	}

}
