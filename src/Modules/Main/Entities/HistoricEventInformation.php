<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing a single historic event information
 *  Used for dating purposes
 */
class HistoricEventInformation
{
    public $eventType = '';
    public $editionNumber = 0;
    public $text = '';
    public $begin = null;
    public $end = null;
    public $remarks = '';


    public function __construct()
    {
    }


    public function setEventType(string $eventType): void
    {
        $this->eventType = $eventType;
    }


    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function setEditionNumber(int $editionNumber): void
    {
        $this->editionNumber = $editionNumber;
    }


    public function getEditionNumber(): string
    {
        return $this->editionNumber;
    }


    public function setText(string $text): void
    {
        $this->text = $text;
    }


    public function getText(): string
    {
        return $this->text;
    }


    public function setBegin(int $begin): void
    {
        $this->begin = $begin;
    }


    public function getBegin(): ?int
    {
        return $this->begin;
    }


    public function setEnd(int $end): void
    {
        $this->end = $end;
    }


    public function getEnd(): ?int
    {
        return $this->end;
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
