<?php

namespace CranachDigitalArchive\Importer\Modules\GraphicRestorations\Entities;

/**
 * Representing a single graphic restoration survey
 */
class Survey
{
    public $type = '';
    public $project = '';
    public $overallAnalysis = '';
    public $remarks = '';
    public $tests = [];
    public $involvedPersons = [];
    public $processingDates = null;
    public $signature = null;


    public function __construct()
    {
    }


    public function setType(string $type)
    {
        $this->type = $type;
    }


    public function getType(): string
    {
        return $this->type;
    }


    public function setProject(string $project)
    {
        $this->project = $project;
    }


    public function getProject(): string
    {
        return $this->project;
    }


    public function setOverallAnalysis(string $overallAnalysis)
    {
        $this->overallAnalysis = $overallAnalysis;
    }


    public function getOverallAnalysis(): string
    {
        return $this->overallAnalysis;
    }


    public function setRemarks(string $remarks)
    {
        $this->remarks = $remarks;
    }


    public function getRemarks(): string
    {
        return $this->remarks;
    }


    public function addTest(Test $test)
    {
        $this->tests[] = $test;
    }


    public function getTests(): array
    {
        return $this->tests;
    }


    public function addInvolvedPerson(Person $involvedPerson)
    {
        $this->involvedPersons[] = $involvedPerson;
    }


    public function getInvolvedPersons(): array
    {
        return $this->involvedPersons;
    }


    public function setProcessingDates(ProcessingDates $processingDates)
    {
        $this->processingDates = $processingDates;
    }


    public function getProcessingDates(): ProcessingDates
    {
        return $this->processingDates;
    }


    public function setSignature(Signature $signature)
    {
        $this->signature = $signature;
    }


    public function getSignature(): Signature
    {
        return $this->signature;
    }
}
