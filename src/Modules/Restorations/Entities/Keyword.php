<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Entities;

/**
 * Representing a single keyword
 */
class Keyword
{
    public $name = '';
    public $additonal = '';


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


    public function setAdditional(string $additonal): void
    {
        $this->additonal = $additonal;
    }


    public function getAdditional(): string
    {
        return $this->additonal;
    }
}
