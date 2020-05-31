<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;


/**
 * Representing a historic event
 */
class Event {

	public $type = '';
	public $dateText = '';
	public $dateBegin = '';
	public $dateEnd = '';
	public $remarks = '';


	function __construct() {

	}


	function setType(string $type) {
		$this->type = $type;
	}


	function getType(): string {
		return $this->type;
	}


	function setDateText(string $dateText) {
		$this->dateText = $dateText;
	}


	function getDateText(): string {
		return $this->dateText;
	}


	function setDateBegin(string $dateBegin) {
		$this->dateBegin = $dateBegin;
	}


	function getDateBegin(): string {
		return $this->dateBegin;
	}


	function setDateEnd(string $dateEnd) {
		$this->dateEnd = $dateEnd;
	}


	function getDateEnd(): string {
		return $this->dateEnd;
	}


	function setRemarks(string $remarks) {
		$this->remarks = $remarks;
	}


	function getRemarks(): string {
		return $this->remarks;
	}

}
