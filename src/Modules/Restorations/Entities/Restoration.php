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
    public $surveys = [
        'artTechExaminations' => [],
        'conditionReports' => [],
        'conservationReports' => [],
        'uncategorizedSurveys' => [],
    ];


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


    public function getSurveys(): array
    {
        return $this->surveys;
    }


    public function addArtTechExamination(Survey $artTechExamination): void
    {
        $this->surveys['artTechExaminations'][] = $artTechExamination;
    }


    public function getArtTechExaminations(): array
    {
        return $this->surveys['artTechExaminations'];
    }


    public function addConditionReport(Survey $conditionReport): void
    {
        $this->surveys['conditionReports'][] = $conditionReport;
    }


    public function getConditionReports(): array
    {
        return $this->surveys['conditionReports'];
    }


    public function addConservationReport(Survey $conservationReport): void
    {
        $this->surveys['conservationReports'][] = $conservationReport;
    }


    public function getConservationReports(): array
    {
        return $this->surveys['conservationReports'];
    }


    public function addUncategorizedSurvey(Survey $uncategorizedSurvey): void
    {
        $this->surveys['uncategorizedSurveys'][] = $uncategorizedSurvey;
    }


    public function getUncategorizedSurveys(): array
    {
        return $this->surveys['uncategorizedSurveys'];
    }
}
