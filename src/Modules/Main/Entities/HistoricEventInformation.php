<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing a single historic event information
 *  Used for dating purposes
 */
class HistoricEventInformation
{
    public $eventType = '';
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


    public function setText(string $text): void
    {
        $this->text = $text;
    }


    public function getText(): string
    {
        return $this->text;
    }


    public function setBegin(string $begin): void
    {
        $this->begin = $begin;
    }


    public function getBegin(): ?string
    {
        return $this->begin;
    }


    public function setEnd(string $end): void
    {
        $this->end = $end;
    }


    public function getEnd(): ?string
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
