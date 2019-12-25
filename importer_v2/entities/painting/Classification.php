<?php

namespace CranachImport\Entities\Painting;


/**
 * Representing a single classifcation, including the condition
 */
class Classification {

	public $classification = '';


	function __construct() {

	}


	function setClassification(string $classification) {
		$this->classification = $classification;
	}


	function getClassification(): string {
		return $this->classification;
	}

}