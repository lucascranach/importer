<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;


/**
 * Representing a publication
 */
class Publication {

	public $type = '';
	public $remarks = '';


	function __construct() {

	}


	function setType(string $type) {
		$this->type = $type;
	}


	function getType(): string {
		return $this->type;
	}


	function setRemarks(string $remarks) {
		$this->remarks = $remarks;
	}


	function getRemarks(): string {
		return $this->remarks;
	}
}
