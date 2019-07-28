<?php

namespace CranachImport\Entities;


/**
 * Representing a single person name detail and type
 * 	A type can be understood as a kind of indicator of how a name differs from the original,
 *  like a wrong spelling
 */
class PersonNameDetail {

	public $name = '';
	public $nameType = '';


	function __construct() {

	}


	function setName(string $name) {
		$this->name = $name;
	}


	function getName(): string {
		return $this->name;
	}


	function setNameType(string $nameType) {
		$this->nameType = $nameType;
	}


	function getNameType(): string {
		return $this->nameType;
	}

}