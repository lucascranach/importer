<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

require_once 'PersonNameDetail.php';


/**
 * Representing a single person name having multiple details
 */
class PersonName
{
    public $constituentId = '';
    public $details = [];


    public function __construct()
    {
    }


    public function setConstituentId(string $constituentId): void
    {
        $this->constituentId = $constituentId;
    }


    public function getConstituentId(): string
    {
        return $this->constituentId;
    }


    public function addDetail(PersonNameDetail $detail): void
    {
        $this->details[] = $detail;
    }


    public function getDetails(): array
    {
        return $this->details;
    }
}
