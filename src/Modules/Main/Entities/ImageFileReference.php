<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing a image file reference
 */
class ImageFileReference
{
    public $type = '';
    public $id = '';

    public function __construct()
    {
    }


    public function setType(string $type): void
    {
        $this->type = $type;
    }


    public function getType(): string
    {
        return $this->type;
    }


    public function setId(string $id): void
    {
        $this->id = $id;
    }


    public function getId(): string
    {
        return $this->id;
    }
}
