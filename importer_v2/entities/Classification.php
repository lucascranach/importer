<?php

namespace CranachImport\Entities;


class Classification {

	public $classification = '';
	public $state = '';


	function __construct() {

	}


	function setClassification(string $classification) {
		$this->classification = $classification;
	}


	function getClassification(): string {
		return $this->classification;
	}


	function setState(string $state) {
		$this->state = $state;
	}


	function getState(): string {
		return $this->state;
	}

}