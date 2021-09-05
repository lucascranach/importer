<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing a single person and their role
 */
class Person
{
    public $displayOrder = 0;
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


    public function setDisplayOrder(int $displayOrder): void
    {
        $this->displayOrder = $displayOrder;
    }


    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }


    public function setId(string $id): void
    {
        $this->id = $id;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function setRole(string $role): void
    {
        $this->role = $role;
    }


    public function getRole(): string
    {
        return $this->role;
    }


    public function setName(string $name): void
    {
        $this->name = $name;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }


    public function getPrefix(): string
    {
        return $this->prefix;
    }


    public function setSuffix(string $suffix): void
    {
        $this->suffix = $suffix;
    }


    public function getSuffix(): string
    {
        return $this->suffix;
    }


    public function setNameType(string $nameType): void
    {
        $this->nameType = $nameType;
    }


    public function getNameType(): string
    {
        return $this->nameType;
    }


    public function setAlternativeName(string $alternativeName): void
    {
        $this->alternativeName = $alternativeName;
    }


    public function getAlternativeName(): string
    {
        return $this->alternativeName;
    }


    public function setRemarks(string $remarks): void
    {
        $this->remarks = $remarks;
    }


    public function getRemarks(): string
    {
        return $this->remarks;
    }


    public function setDate(string $date): void
    {
        $this->date = $date;
    }


    public function getDate(): string
    {
        return $this->date;
    }


    public function setIsUnknown(bool $isUnknown): void
    {
        $this->isUnknown = $isUnknown;
    }


    public function getIsUnknown(): bool
    {
        return $this->isUnknown;
    }
}
