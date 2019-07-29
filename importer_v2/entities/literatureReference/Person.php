<?php

namespace CranachImport\Entities\LiteratureReference;


/**
 * Representing an person
 */
class Person {

	public $role = '';
	public $name = '';


	function __construct() {

	}


	function setRole(string $role) {
		$this->role = $role;
	}


	function getRole(): string {
		return $this->role;
	}


	function setName(string $name) {
		$this->name = $name;
	}


	function getName(): string {
		return $this->name;
	}
}
