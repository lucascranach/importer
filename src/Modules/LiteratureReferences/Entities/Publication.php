<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;

/**
 * Representing a publication
 */
class Publication
{
    public $type = '';
    public $remarks = '';


    public function __construct()
    {
    }


    public function setType(string $type)
    {
        $this->type = $type;
    }


    public function getType(): string
    {
        return $this->type;
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
