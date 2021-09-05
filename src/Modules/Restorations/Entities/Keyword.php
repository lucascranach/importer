<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Entities;

/**
 * Representing a single keyword
 */
class Keyword
{
    public $id = '';
    public $name = '';
    public $additional = '';


    public function __construct()
    {
    }


    public function setId(string $id): void
    {
        $this->id = $id;
    }


    public function getId(): string
    {
        return $this->id;
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
