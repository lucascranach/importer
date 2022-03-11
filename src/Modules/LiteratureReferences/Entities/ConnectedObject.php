<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;

/**
 * Representing an connected object
 */
class ConnectedObject
{
    private static $inventoryNumberPrefixPatterns = [
        '/^GWN_/' => 'GWN_',
        '/^CDA\./' => 'CDA.',
        '/^CDA_/' => 'CDA_',
        '/^G_G_/' => 'G_G_',
        '/^G_/' => 'G_',
    ];

    public $inventoryNumberPrefix = '';
    public $inventoryNumber = '';
    public $catalogNumber = '';
    public $pageNumber = '';
    public $figureNumber = '';
    public $remarks = '';


    public function __construct()
    {
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

        foreach (self::$inventoryNumberPrefixPatterns as $pattern => $value) {
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


    public function setCatalogNumber(string $catalogNumber): void
    {
        $this->catalogNumber = $catalogNumber;
    }


    public function getCatalogNumber(): string
    {
        return $this->catalogNumber;
    }


    public function setPageNumber(string $pageNumber): void
    {
        $this->pageNumber = $pageNumber;
    }


    public function getPageNumber(): string
    {
        return $this->pageNumber;
    }

    public function setFigureNumber(string $figureNumber): void
    {
        $this->figureNumber = $figureNumber;
    }


    public function getFigureNumber(): string
    {
        return $this->figureNumber;
    }

    public function setRemarks(string $remarks): void
    {
        $this->remarks = $remarks;
    }


    public function getRemarks(): string
    {
        return $this->remarks;
    }
}
