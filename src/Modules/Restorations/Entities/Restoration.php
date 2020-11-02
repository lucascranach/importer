<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Entities;

use CranachDigitalArchive\Importer\Interfaces\Entities\ILanguageBaseItem;

/**
 * Representing a single restoration overview and all its data
 */
class Restoration implements ILanguageBaseItem
{
    public $langCode = '<unknown language>';

    public $inventoryNumber = '';
    public $objectId = '';
    public $surveys = [];


    public function __construct()
    {
    }

    public function getId(): string
    {
        return $this->getInventoryNumber();
    }


    /**
     * @return void
     */
    public function setLangCode(string $langCode)
    {
        $this->langCode = $langCode;
    }


    public function getLangCode(): string
    {
        return $this->langCode;
    }


    public function setInventoryNumber(string $inventoryNumber): void
    {
        $this->inventoryNumber = $inventoryNumber;
    }


    public function getInventoryNumber(): string
    {
        return $this->inventoryNumber;
    }


    public function setObjectId(int $objectId): void
    {
        $this->objectId = $objectId;
    }


    public function getObjectId(): int
    {
        return $this->objectId;
    }


    public function addSurvey(Survey $survey): void
    {
        $this->surveys[] = $survey;
    }


    public function getSurveys(): array
    {
        return $this->surveys;
    }
}
