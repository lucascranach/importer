<?php

namespace CranachImport\Entities;


/**
 * Representing the structure of a single additional text information
 */
class AdditionalTextInformation {

	public $type = '';
	public $text = '';
	public $year = null;


	function __construct() {

	}

	function setType(string $type) {
		$this->type = $type;
	}


	function getType(): string {
		return $this->type;
	}


	function setText(string $text) {
		$this->text = $text;
	}


	function getText(): string {
		return $this->text;
	}


	function setYear(int $year) {
		$this->year = $year;
	}


	function getYear(): ?int {
		return $this->year;
	}

}
