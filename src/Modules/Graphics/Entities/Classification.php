<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Entities;

/**
 * Representing a single classifcation, including the condition
 */
class Classification
{
    public $classification = '';
    public $condition = '';
    public $printProcess = '';


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


    public function setCondition(string $condition): void
    {
        $this->condition = $condition;
    }


    public function getCondition(): string
    {
        return $this->condition;
    }


    public function setPrintProcess(string $printProcess): void
    {
        $this->printProcess = $printProcess;
    }


    public function getPrintProcess(): string
    {
        return $this->printProcess;
    }
}
