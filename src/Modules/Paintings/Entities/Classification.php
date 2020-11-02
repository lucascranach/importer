<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Entities;

/**
 * Representing a single classifcation, including the condition
 */
class Classification
{
    public $classification = '';


    public function __construct()
    {
    }


    public function setClassification(string $classification): void
    {
        $this->classification = $classification;
    }


    public function getClassification(): string
    {
        return $this->classification;
    }
}
