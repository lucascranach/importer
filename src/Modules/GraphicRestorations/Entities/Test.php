<?php

namespace CranachDigitalArchive\Importer\Modules\GraphicRestorations\Entities;

/**
 * Representing a single graphic restoration test
 */
class Test
{
    public $kind = '';
    public $text = '';
    public $purpose = '';
    public $remarks = '';


    public function __construct()
    {
    }


    public function setKind(string $kind)
    {
        $this->kind = $kind;
    }


    public function getKind(): string
    {
        return $this->kind;
    }


    public function setText(string $text)
    {
        $this->text = $text;
    }


    public function getText(): string
    {
        return $this->text;
    }


    public function setPurpose(string $purpose)
    {
        $this->purpose = $purpose;
    }


    public function getPurpose(): string
    {
        return $this->purpose;
    }


    public function setRemarks(string $remarks)
    {
        $this->remarks = $remarks;
    }


    public function getRemarks(): string
    {
        return $this->remarks;
    }
}
