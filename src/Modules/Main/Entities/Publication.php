<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing a single publication
 */
class Publication
{
    public $title = '';
    public $pageNumber = '';
    public $referenceId = '';


    public function __construct()
    {
    }


    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    public function getTitle(): string
    {
        return $this->title;
    }


    public function setPageNumber(string $pageNumber): void
    {
        $this->pageNumber = $pageNumber;
    }


    public function getPageNumber(): string
    {
        return $this->pageNumber;
    }


    public function setReferenceId(string $referenceId): void
    {
        $this->referenceId = $referenceId;
    }


    public function getReferenceId(): string
    {
        return $this->referenceId;
    }
}
