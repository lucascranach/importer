<?php

namespace CranachImport\Modules\Main\Entities;


/**
 * Representing the title of a work including remarks to the title
 */
class Title {

	public $type = '';
	public $title = '';
	public $remarks = '';


	function __construct() {

	}


	function setType(string $type) {
		$this->type = $type;
	}


	function getType(): string {
		return $this->type;
	}


	function setTitle(string $title) {
		$this->title = $title;
	}


	function getTitle(): string {
		return $this->title;
	}


	function setRemarks(string $remarks) {
		$this->remarks = $remarks;
	}


	function getRemarks(): string {
		return $this->remarks;
	}

}
