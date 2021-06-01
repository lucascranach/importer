<?php

namespace CranachDigitalArchive\Importer\Modules\Filters\Entities;

use CranachDigitalArchive\Importer\Language;

/**
 * Representing a single filter item
 */
class Filter
{
    public $id = '';
    public $text = [
        Language::DE => '',
        Language::EN => '',
    ];
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


    public function getText(): array
    {
        return $this->text;
    }


    public function setText(array $text)
    {
        $this->text = $text;
    }



    public function getLangText(string $langCode): ?string
    {
        return isset($this->text[$langCode]) ? $this->text[$langCode] : null;
    }


    public function setLangText(string $langCode, string $text)
    {
        $this->text[$langCode] = $text;
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
