<?php

namespace CranachImport\Entities;

require_once 'PersonNameDetail.php';


class PersonName {

	public $constituentId = '';
	public $details = [];


	function __construct() {

	}


	function setConstituentId(string $constituentId) {
		$this->constituentId = $constituentId;
	}


	function getConstituentId(): string {
		return $this->constituentId;
	}


	function addDetail(PersonNameDetail $detail) {
		$this->details[] = $detail;
	}


	function getDetails(): array {
		return $this->details;
	}

}