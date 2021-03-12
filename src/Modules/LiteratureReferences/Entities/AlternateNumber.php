<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;

/**
 * Representing an alternate number
 */
class AlternateNumber
{
    public $description = '';
    public $number = '';
    public $remarks = '';


    public function __construct()
    {
    }


    public function setDescription(string $description): void
    {
        $this->description = $description;
    }


    public function getDescription(): string
    {
        return $this->description;
    }


    public function setNumber(string $number): void
    {
        $this->number = $number;
    }


    public function getNumber(): string
    {
        return $this->number;
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
