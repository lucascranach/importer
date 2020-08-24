<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing a single person name detail and type
 * 	A type can be understood as a kind of indicator of how a name differs from the original,
 *  like a wrong spelling
 */
class PersonNameDetail
{
    public $name = '';
    public $nameType = '';


    public function __construct()
    {
    }


    public function setName(string $name)
    {
        $this->name = $name;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function setNameType(string $nameType)
    {
        $this->nameType = $nameType;
    }


    public function getNameType(): string
    {
        return $this->nameType;
    }
}
