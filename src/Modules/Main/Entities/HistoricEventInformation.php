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


    public function setEventType(string $eventType)
    {
        $this->eventType = $eventType;
    }


    public function getEventType(): string
    {
        return $this->eventType;
    }


    public function setText(string $text)
    {
        $this->text = $text;
    }


    public function getText(): string
    {
        return $this->text;
    }


    public function setBegin(int $begin)
    {
        $this->begin = $begin;
    }


    public function getBegin(): ?int
    {
        return $this->begin;
    }


    public function setEnd(int $end)
    {
        $this->end = $end;
    }


    public function getEnd(): ?int
    {
        return $this->end;
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
