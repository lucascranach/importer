<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Entities;

/**
 * Representing a single keyword
 */
class Keyword
{
    public $name = '';
    public $additional = '';


    public function __construct()
    {
    }


    public function setName(string $name): void
    {
        $this->name = $name;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function setAdditional(string $additional): void
    {
        $this->additional = $additional;
    }


    public function getAdditional(): string
    {
        return $this->additional;
    }
}
