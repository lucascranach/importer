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


    public function setTitle(string $title)
    {
        $this->title = $title;
    }


    public function getTitle(): string
    {
        return $this->title;
    }


    public function setPageNumber(string $pageNumber)
    {
        $this->pageNumber = $pageNumber;
    }


    public function getPageNumber(): string
    {
        return $this->pageNUmber;
    }


    public function setReferenceId(string $referenceId)
    {
        $this->referenceId = $referenceId;
    }


    public function getReferenceId(): string
    {
        return $this->referenceId;
    }
}
