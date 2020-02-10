<?php

namespace CranachImport\Modules\Graphics\Entities;


/**
 * Representing a single classifcation, including the condition
 */
class Classification {

	public $classification = '';
	public $condition = '';


	function __construct() {

	}


	function setClassification(string $classification) {
		$this->classification = $classification;
	}


	function getClassification(): string {
		return $this->classification;
	}


	function setCondition(string $condition) {
		$this->condition = $condition;
	}


	function getCondition(): string {
		return $this->condition;
	}

}