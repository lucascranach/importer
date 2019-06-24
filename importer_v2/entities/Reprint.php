<?php

namespace CranachImport\Entities;


/**
 * Representing a single reprint information referencing the original work
 */
class Reprint {

	public $text = '';
	public $inventoryNumber = '';
	public $remark = '';


	function __construct() {

	}


	function setText(string $text) {
		$this->text = $text;
	}


	function getText(): string {
		return $this->text;
	}


	function setInventoryNumber(string $inventoryNumber) {
		$this->inventoryNumber = $inventoryNumber;
	}


	function getInventoryNumber(): string {
		return $this->inventoryNumber;
	}


	function setRemark(string $remark) {
		$this->remark = $remark;
	}


	function getRemark(): string {
		return $this->remark;
	}

}
