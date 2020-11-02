<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Entities;

/**
 * Representing a archival dating
 */
class Dating
{
    public $dated = '';
    public $begin = null;
    public $end = null;


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
}
