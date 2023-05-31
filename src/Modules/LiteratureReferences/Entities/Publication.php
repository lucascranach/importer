<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;

/**
 * Representing a publication
 */
class Publication
{
    public $type = '';
    public $text = '';
    public $remarks = '';
    /* @var Publication[] */
    public $subPublications = [];


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


    public function setText(string $text): void
    {
        $this->text = $text;
    }


    public function getText(): string
    {
        return $this->text;
    }


    public function setRemarks(string $remarks): void
    {
        $this->remarks = $remarks;
    }


    public function getRemarks(): string
    {
        return $this->remarks;
    }


    public function setSubPublications(array $subPublications): void
    {
        $this->subPublications = $subPublications;
    }


    public function addSubPublication(Publication $subPublication): void
    {
        $this->subPublications[] = $subPublication;
    }


    public function getSubPublications(): array
    {
        return $this->subPublications;
    }
}
