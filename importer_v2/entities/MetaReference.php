<?php

namespace CranachImport\Entities;


class MetaReference {

	public $type = '';
	public $term = '';
	public $path = '';


	function __construct() {

	}


	function setType(string $type) {
		$this->type = $type;
	}


	function getType(): string {
		return $this->type;
	}


	function setTerm(string $term) {
		$this->term = $term;
	}


	function getTerm(): string {
		return $this->term;
	}


	function setPath(string $path) {
		$this->path = $path;
	}


	function getPath(): string {
		return $this->path;
	}

}
