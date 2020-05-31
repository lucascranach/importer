<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing a single catalog work refrence
 */
class CatalogWorkReference
{
    public $description = '';
    public $referenceNumber = '';
    public $remarks = '';


    public function __construct()
    {
    }


    public function setDescription(string $description)
    {
        $this->description = $description;
    }


    public function getDescription(): string
    {
        return $this->description;
    }


    public function setReferenceNumber(string $referenceNumber)
    {
        $this->referenceNumber = $referenceNumber;
    }


    public function getReferenceNumber(): string
    {
        return $this->referenceNumber;
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
