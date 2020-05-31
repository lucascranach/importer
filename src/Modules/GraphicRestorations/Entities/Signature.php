<?php

namespace CranachDigitalArchive\Importer\Modules\GraphicRestorations\Entities;

/**
 * Representing a single signature
 */
class Signature
{
    public $date = '';
    public $name = '';


    public function __construct()
    {
    }


    public function setDate(string $date)
    {
        $this->date = $date;
    }


    public function getDate(): string
    {
        return $this->date;
    }


    public function setName(string $name)
    {
        $this->name = $name;
    }


    public function getName(): string
    {
        return $this->name;
    }
}
