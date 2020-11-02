<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Entities;

/**
 * Representing a single graphic restoration processing date and year set
 */
class ProcessingDates
{
    public $beginDate = '';
    public $beginYear = null;
    public $endDate = '';
    public $endYear = null;


    public function __construct()
    {
    }


    public function setBeginDate(string $beginDate): void
    {
        $this->beginDate = $beginDate;
    }


    public function getBeginDate(): string
    {
        return $this->beginDate;
    }


    public function setBeginYear(int $beginYear): void
    {
        $this->beginYear = $beginYear;
    }


    public function getBeginYear(): ?int
    {
        return $this->beginYear;
    }


    public function setEndDate(string $endDate): void
    {
        $this->endDate = $endDate;
    }


    public function getEndDate(): string
    {
        return $this->endDate;
    }


    public function setEndYear(int $endYear): void
    {
        $this->endYear = $endYear;
    }


    public function getEndYear(): ?int
    {
        return $this->endYear;
    }
}
