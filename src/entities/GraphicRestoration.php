<?php

namespace CranachImport\Entities;

require_once 'IBaseItem.php';

require_once 'graphicRestoration/Survey.php';

use CranachImport\Entities\IBaseItem;


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


	function addSurvey(GraphicRestoration\Survey $survey) {
		$this->surveys[] = $survey;
	}


	function getSurveys(): array {
		return $this->surveys;
	}

}