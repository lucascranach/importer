<?php

namespace CranachImport\Entities\GraphicRestoration;


/**
 * Representing a single graphic restoration test
 */
class Test {

	public $kind = '';
	public $text = '';
	public $purpose = '';
	public $remarks = '';


	function __construct() {

	}


	function setKind(string $kind) {
		$this->kind = $kind;
	}


	function getKind(): string {
		return $this->kind;
	}


	function setText(string $text) {
		$this->text = $text;
	}


	function getText(): string {
		return $this->text;
	}


	function setPurpose(string $purpose) {
		$this->purpose = $purpose;
	}


	function getPurpose(): string {
		return $this->purpose;
	}


	function setRemarks(string $remarks) {
		$this->remarks = $remarks;
	}


	function getRemarks(): string {
		return $this->remarks;
	}

}