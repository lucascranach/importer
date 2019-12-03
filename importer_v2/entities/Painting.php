<?php

namespace CranachImport\Entities;

require_once 'ILanguageBaseItem.php';

require_once 'main/Person.php';
require_once 'main/PersonName.php';
require_once 'main/Title.php';

require_once 'painting/Classification.php';

use CranachImport\Entities\ILanguageBaseItem;


/**
 * Representing a single graphic and all its data
 * 	One instance containing only data for one language
 */
class Painting implements ILanguageBaseItem {

	public $langCode = '<unknown language>';

	public $involvedPersons = [];
	public $involvedPersonsNames = []; // Evtl. Umbau notwendig

	public $titles = [];
	public $classification = null;
	public $objectName = '';
	public $inventoryNumber = '';
	public $objectId = null;


	function __construct() {

	}


	function setLangCode(string $langCode) {
		$this->langCode = $langCode;
	}


	function getLangCode(): string {
		return $this->langCode;
	}


	function addPerson(Main\Person $person) {
		$this->involvedPersons[] = $person;
	}


	function getPersons(): array {
		$this->involvedPersons;
	}


	function addPersonName(Main\PersonName $personName) {
		$this->involvedPersonsNames[] = $personName;
	}


	function getPersonNames(): array {
		$this->involvedPersonsNames;
	}

	
	function addTitle(Main\Title $title) {
		$this->titles[] = $title;
	}


	function getTitles(): array {
		return $this->titles;
	}


	function setClassification(Painting\Classification $classification) {
		$this->classification = $classification;
	}


	function getClassification(): Painting\Classification {
		return $this->classification;
	}

	
	function setObjectName(string $objectName) {
		$this->objectName = $objectName;
	}


	function getObjectName(): string {
		return $this->objectName;
	}


	function setInventoryNumber(string $inventoryNumber) {
		$this->inventoryNumber = $inventoryNumber;
	}


	function getInventoryNumber(): string {
		return $this->inventoryNumber;
	}


	function setObjectId(int $objectId) {
		$this->objectId = $objectId;
	}


	function getObjectId(): int {
		return $this->objectId;
	}

}