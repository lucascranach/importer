<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Entities;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;

/**
 * Representing a single restoration overview and all its data
 */
class Restoration implements IBaseItem
{
    const INVENTORY_NUMBER_PREFIX_PATTERNS = [
        '/^GWN_/' => 'GWN_',
        '/^CDA\./' => 'CDA.',
        '/^CDA_/' => 'CDA_',
        '/^G_G_/' => 'G_G_',
        '/^G_/' => 'G_',
    ];

    public $langCode = '';
    public $inventoryNumberPrefix = '';
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


    public function setLangCode(string $langCode): void
    {
        $this->langCode = $langCode;
    }


    public function getLangCode(): string
    {
        return $this->langCode;
    }


    public function setInventoryNumberPrefix(string $inventoryNumberPrefix): void
    {
        $this->inventoryNumberPrefix = $inventoryNumberPrefix;
    }


    public function getInventoryNumberPrefix(): string
    {
        return $this->inventoryNumberPrefix;
    }


    public function setInventoryNumber(string $inventoryNumber): void
    {
        $this->inventoryNumber = $inventoryNumber;

        foreach (self::INVENTORY_NUMBER_PREFIX_PATTERNS as $pattern => $value) {
            $count = 0;

            $this->inventoryNumber = preg_replace($pattern, '', $this->inventoryNumber, -1, $count);

            if ($count > 0) {
                $this->setInventoryNumberPrefix($value);
                break;
            }
        }
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


    public function addSurvey(Survey $survey)
    {
        $this->surveys[] = $survey;
    }


    public function getSurveys(): array
    {
        return $this->surveys;
    }
}
