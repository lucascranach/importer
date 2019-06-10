<?php

namespace CranachImport\Entities;


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