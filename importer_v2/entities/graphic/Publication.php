<?php

namespace CranachImport\Entities;


/**
 * Representing a single publication
 */
class Publication {

	public $title = '';
	public $pageNumber = '';
	public $referenceId = '';


	function __construct() {

	}


	function setTitle(string $title) {
		$this->title = $title;
	}


	function getTitle(): string {
		return $this->title;
	}


	function setPageNumber(string $pageNumber) {
		$this->pageNumber = $pageNumber;
	}


	function getPageNumber(): string {
		return $this->pageNUmber;
	}


	function setReferenceId(string $referenceId) {
		$this->referenceId = $referenceId;
	}


	function getReferenceId(): string {
		return $this->referenceId;
	}

}
