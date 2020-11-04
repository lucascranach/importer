<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;

/**
 * Representing a historic event
 */
class Event
{
    public $type = '';
    public $dateText = '';
    public $dateBegin = '';
    public $dateEnd = '';
    public $remarks = '';


    public function __construct()
    {
    }


    public function setType(string $type): void
    {
        $this->type = $type;
    }


    public function getType(): string
    {
        return $this->type;
    }


    public function setDateText(string $dateText): void
    {
        $this->dateText = $dateText;
    }


    public function getDateText(): string
    {
        return $this->dateText;
    }


    public function setDateBegin(string $dateBegin): void
    {
        $this->dateBegin = $dateBegin;
    }


    public function getDateBegin(): string
    {
        return $this->dateBegin;
    }


    public function setDateEnd(string $dateEnd): void
    {
        $this->dateEnd = $dateEnd;
    }


    public function getDateEnd(): string
    {
        return $this->dateEnd;
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
