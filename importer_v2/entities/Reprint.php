<?php

namespace CranachImport\Entities;


class Reprint {

	public $inventoryNumber = '';
	public $remark = '';


	function __construct() {

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
