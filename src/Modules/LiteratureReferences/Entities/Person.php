<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;

/**
 * Representing an person
 */
class Person
{
    public $role = '';
    public $name = '';


    public function __construct()
    {
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
}
