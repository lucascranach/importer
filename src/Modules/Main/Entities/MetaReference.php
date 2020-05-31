<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing a single meta reference
 */
class MetaReference
{
    public $type = '';
    public $term = '';
    public $path = '';


    public function __construct()
    {
    }


    public function setType(string $type)
    {
        $this->type = $type;
    }


    public function getType(): string
    {
        return $this->type;
    }


    public function setTerm(string $term)
    {
        $this->term = $term;
    }


    public function getTerm(): string
    {
        return $this->term;
    }


    public function setPath(string $path)
    {
        $this->path = $path;
    }


    public function getPath(): string
    {
        return $this->path;
    }
}
