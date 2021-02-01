<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Entities;

/**
 * Representing a single graphic restoration test
 */
class Test
{
    public $kind = '';
    public $text = '';
    public $purpose = '';
    public $keywords = [];


    public function __construct()
    {
    }


    public function setKind(string $kind): void
    {
        $this->kind = $kind;
    }


    public function getKind(): string
    {
        return $this->kind;
    }


    public function setText(string $text): void
    {
        $this->text = $text;
    }


    public function getText(): string
    {
        return $this->text;
    }


    public function setPurpose(string $purpose): void
    {
        $this->purpose = $purpose;
    }


    public function getPurpose(): string
    {
        return $this->purpose;
    }


    public function addKeyword(Keyword $keyword)
    {
        $this->keywords[] = $keyword;
    }


    public function getKeywords(): array
    {
        return $this->keywords;
    }
}
