<?php

namespace CranachDigitalArchive\Importer\Modules\GraphicRestorations\Entities;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;

/**
 * Representing a single graphic restoration and all its data
 */
class GraphicRestoration implements IBaseItem
{
    public $inventoryNumber = '';
    public $objectId = '';
    public $surveys = [];


    public function __construct()
    {
    }


    public function setInventoryNumber(string $inventoryNumber)
    {
        $this->inventoryNumber = $inventoryNumber;
    }


    public function getInventoryNumber(): string
    {
        return $this->inventoryNumber;
    }


    public function setObjectId(int $objectId)
    {
        $this->objectId = $objectId;
    }


    public function getObjectId(): int
    {
        return $this->objectId;
    }


    public function addSurvey(Survey $survey)
    {
        $this->surveys[] = $survey;
    }


    public function getSurveys(): array
    {
        return $this->surveys;
    }
}
