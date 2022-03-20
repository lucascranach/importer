<?php

namespace CranachDigitalArchive\Importer\Modules\Filters\Entities;

/**
 * Representing a single filter item
 */
class Filter
{
    public $id = '';
    public $text = '';
    public $children = [];


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


    public function getText(): string
    {
        return $this->text;
    }


    public function setText(string $text)
    {
        $this->text = $text;
    }


    public function getChildren(): array
    {
        return $this->children;
    }


    public function setChildren(array $children)
    {
        $this->children = $children;
    }


    public function addChild(Filter $child)
    {
        $this->children[] = $child;
    }
}
