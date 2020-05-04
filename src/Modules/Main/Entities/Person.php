<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;


/**
 * Representing a single person and their role
 */
class Person {

	public $id = '';
	public $role = '';
	public $name = '';
	public $prefix = '';
	public $suffix = '';
	public $nameType = '';
	public $alternativeName = '';
	public $remarks = '';
	public $date = '';

	public $isUnknown = false;


	function __construct() {

	}


	function setId(string $id) {
		$this->id = $id;
	}


	function getId(): string {
		return $this->id;
	}


	function setRole(string $role) {
		$this->role = $role;
	}


	function getRole(): string {
		return $this->role;
	}


	function setName(string $name) {
		$this->name = $name;
	}


	function getName(): string {
		return $this->name;
	}


	function setPrefix(string $prefix) {
		$this->prefix = $prefix;
	}


	function getPrefix(): string {
		return $this->prefix;
	}


	function setSuffix(string $suffix) {
		$this->suffix = $suffix;
	}


	function getSuffix(): string {
		return $this->suffix;
	}


	function setNameType(string $nameType) {
		$this->nameType = $nameType;
	}


	function getNameType(): string {
		return $this->nameType;
	}


	function setAlternativeName(string $alternativeName) {
		$this->alternativeName = $alternativeName;
	}


	function getAlternativeName(): string {
		return $this->alternativeName;
	}


	function setRemarks(string $remarks) {
		$this->remarks = $remarks;
	}


	function getRemarks(): string {
		return $this->remarks;
	}


	function setDate(string $date) {
		$this->date = $date;
	}


	function getDate(): string {
		return $this->date;
	}


	function setIsUnknown(bool $isUnknown) {
		$this->isUnknown = $isUnknown;
	}


	function getIsUnknown(): bool {
		return $this->isUnknown;
	}

}
