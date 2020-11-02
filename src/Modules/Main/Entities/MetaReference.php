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
    public $url = '';


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


    public function setTerm(string $term): void
    {
        $this->term = $term;
    }


    public function getTerm(): string
    {
        return $this->term;
    }


    public function setPath(string $path): void
    {
        $this->path = $path;
    }


    public function getPath(): string
    {
        return $this->path;
    }


    public function setURL(string $url): void
    {
        $this->url = $url;
    }


    public function getURL(): string
    {
        return $this->url;
    }
}
