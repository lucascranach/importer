<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;

/**
 * Representing an connected object
 */
class ConnectedObject
{
    public $inventoryNumber = '';
    public $catalogNumber = '';
    public $pageNumber = '';
    public $figureNumber = '';
    public $remarks = '';


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


    public function setCatalogNumber(string $catalogNumber)
    {
        $this->catalogNumber = $catalogNumber;
    }


    public function getCatalogNumber(): string
    {
        return $this->catalogNumber;
    }


    public function setPageNumber(string $pageNumber)
    {
        $this->pageNumber = $pageNumber;
    }


    public function getPageNumber(): string
    {
        return $this->pageNumber;
    }

    public function setFigureNumber(string $figureNumber)
    {
        $this->figureNumber = $figureNumber;
    }


    public function getFigureNumber(): string
    {
        return $this->figureNumber;
    }

    public function setRemarks(string $remarks)
    {
        $this->remarks = $remarks;
    }


    public function getRemarks(): string
    {
        return $this->remarks;
    }
}
