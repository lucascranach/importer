<?php

namespace CranachImport\Modules\Main\Entities;


/**
 * Representing a the dimension in a structured way
 */
class StructuredDimension {

	public $element = '';
	public $width = null;
	public $height = null;


	function __construct() {

	}


	function setElement(string $element) {
		$this->element = $element;
	}


	function getElement(): string {
		return $this->element;
	}


	function setWidth(string $width) {
		$this->width = $width;
	}


	function getWidth(): string {
		return $this->width;
	}


	function setHeight(string $height) {
		$this->height = $height;
	}


	function getHeight(): string {
		return $this->height;
	}

}