<?php

namespace CranachImport\Modules\Main\Entities;


/**
 * Representing the structure of a single additional text information
 */
class AdditionalTextInformation {

	public $type = '';
	public $text = '';
	public $date = '';
	public $year = null;
	public $author = '';


	function __construct() {

	}

	function setType(string $type) {
		$this->type = $type;
	}


	function getType(): string {
		return $this->type;
	}


	function setText(string $text) {
		$this->text = $text;
	}


	function getText(): string {
		return $this->text;
	}


	function setDate(string $date) {
		$this->date = $date;
	}


	function getDate(): string {
		return $this->date;
	}


	function setYear(int $year) {
		$this->year = $year;
	}


	function getYear(): ?int {
		return $this->year;
	}


	function setAuthor(string $author) {
		$this->author = $author;
	}


	function getAuhtor(): ?string {
		return $this->author;
	}

}
