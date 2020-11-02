<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing a dating including multiple historic event informations
 */
class Dating
{
    public $dated = '';
    public $begin = null;
    public $end = null;
    public $remarks = '';
    public $historicEventInformations = [];


    public function __construct()
    {
    }


    public function setDated(string $dated): void
    {
        $this->dated = $dated;
    }


    public function getDated(): string
    {
        return $this->dated;
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


    public function setRemarks(string $remark): void
    {
        $this->remarks = $remark;
    }


    public function getRemarks(): string
    {
        return $this->remarks;
    }


    public function addHistoricEventInformation(HistoricEventInformation $historicEventInformation): void
    {
        $this->historicEventInformations[] = $historicEventInformation;
    }


    public function getHistoricEventInformations(): array
    {
        return $this->historicEventInformations;
    }
}
