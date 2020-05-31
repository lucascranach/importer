<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing a single person and their role
 */
class Person
{
    public $id = '';
    public $role = '';
    public $name = '';
    public $prefix = '';
    public $suffix = '';
    public $nameType = '';
    public $alternativeName = '';
    public $remarks = '';
    public $date = '';

    public $isUnknown = false;


    public function __construct()
    {
    }


    public function setId(string $id)
    {
        $this->id = $id;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function setRole(string $role)
    {
        $this->role = $role;
    }


    public function getRole(): string
    {
        return $this->role;
    }


    public function setName(string $name)
    {
        $this->name = $name;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }


    public function getPrefix(): string
    {
        return $this->prefix;
    }


    public function setSuffix(string $suffix)
    {
        $this->suffix = $suffix;
    }


    public function getSuffix(): string
    {
        return $this->suffix;
    }


    public function setNameType(string $nameType)
    {
        $this->nameType = $nameType;
    }


    public function getNameType(): string
    {
        return $this->nameType;
    }


    public function setAlternativeName(string $alternativeName)
    {
        $this->alternativeName = $alternativeName;
    }


    public function getAlternativeName(): string
    {
        return $this->alternativeName;
    }


    public function setRemarks(string $remarks)
    {
        $this->remarks = $remarks;
    }


    public function getRemarks(): string
    {
        return $this->remarks;
    }


    public function setDate(string $date)
    {
        $this->date = $date;
    }


    public function getDate(): string
    {
        return $this->date;
    }


    public function setIsUnknown(bool $isUnknown)
    {
        $this->isUnknown = $isUnknown;
    }


    public function getIsUnknown(): bool
    {
        return $this->isUnknown;
    }
}
