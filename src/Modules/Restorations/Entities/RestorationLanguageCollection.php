<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Entities;

use CranachDigitalArchive\Importer\AbstractItemLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Restorations\Interfaces\IRestoration;

class RestorationLanguageCollection extends AbstractItemLanguageCollection implements IRestoration
{
    protected function createItem(): IRestoration
    {
        return new Restoration();
    }

    public function getId(): string
    {
        return $this->getInventoryNumber();
    }


    public function setLangCode(string $langCode): void
    {
        foreach ($this as $restoration) {
            $restoration->setLangCode($langCode);
        }
    }


    public function getLangCode(): string
    {
        return $this->first()->langCode;
    }


    public function setInventoryNumberPrefix(string $inventoryNumberPrefix): void
    {
        foreach ($this as $restoration) {
            $restoration->setInventoryNumberPrefix($inventoryNumberPrefix);
        }
    }


    public function getInventoryNumberPrefix(): string
    {
        return $this->first()->inventoryNumberPrefix;
    }


    public function setInventoryNumber(string $inventoryNumber): void
    {
        foreach ($this as $restoration) {
            $restoration->setInventoryNumber($inventoryNumber);
        }
    }


    public function getInventoryNumber(): string
    {
        return $this->first()->inventoryNumber;
    }


    public function setObjectId(int $objectId): void
    {
        foreach ($this as $restoration) {
            $restoration->setObjectId($objectId);
        }
    }


    public function getObjectId(): int
    {
        return $this->first()->objectId;
    }


    public function addSurvey(Survey $survey)
    {
        foreach ($this as $restoration) {
            $restoration->addSurvey($survey);
        }
    }


    public function getSurveys(): array
    {
        return $this->first()->surveys;
    }
}
