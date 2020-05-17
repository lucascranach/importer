<?php

namespace CranachDigitalArchive\Importer\Modules\GraphicRestorations\Entities;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;


/**
 * Representing a single graphic restoration and all its data
 */
class GraphicRestoration implements IBaseItem {

	public $inventoryNumber = '';
	public $objectId = '';
	public $surveys = [];


	function __construct() {

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


	function addSurvey(Survey $survey) {
		$this->surveys[] = $survey;
	}


	function getSurveys(): array {
		return $this->surveys;
	}

}