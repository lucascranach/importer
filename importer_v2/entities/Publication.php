<?php

namespace CranachImport\Entities;


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


	function setPageNUmber(string $pageNUmber) {
		$this->pageNUmber = $pageNUmber;
	}


	function getPageNUmber(): string {
		return $this->pageNUmber;
	}


	function setReferenceId(string $referenceId) {
		$this->referenceId = $referenceId;
	}


	function getReferenceId(): string {
		return $this->referenceId;
	}

}
