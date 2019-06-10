<?php

namespace CranachImport\Entities;


class Title {

	public $title = '';
	public $remark = '';


	function __construct() {

	}


	function setTitle(string $title) {
		$this->title = $title;
	}


	function getTitle(): string {
		return $this->title;
	}


	function setRemark(string $remark) {
		$this->remark = $remark;
	}


	function getRemark(): string {
		return $this->remark;
	}

}
