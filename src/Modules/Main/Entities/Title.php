<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing the title of a work including remarks to the title
 */
class Title
{
    public $type = '';
    public $title = '';
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


    public function setTitle(string $title)
    {
        $this->title = $title;
    }


    public function getTitle(): string
    {
        return $this->title;
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
