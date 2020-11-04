<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Entities;

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


    public function setType(string $type): void
    {
        $this->type = $type;
    }


    public function getType(): string
    {
        return $this->type;
    }


    public function setProject(string $project): void
    {
        $this->project = $project;
    }


    public function getProject(): string
    {
        return $this->project;
    }


    public function setOverallAnalysis(string $overallAnalysis): void
    {
        $this->overallAnalysis = $overallAnalysis;
    }


    public function getOverallAnalysis(): string
    {
        return $this->overallAnalysis;
    }


    public function setRemarks(string $remarks): void
    {
        $this->remarks = $remarks;
    }


    public function getRemarks(): string
    {
        return $this->remarks;
    }


    public function addTest(Test $test): void
    {
        $this->tests[] = $test;
    }


    public function getTests(): array
    {
        return $this->tests;
    }


    public function addInvolvedPerson(Person $involvedPerson): void
    {
        $this->involvedPersons[] = $involvedPerson;
    }


    public function getInvolvedPersons(): array
    {
        return $this->involvedPersons;
    }


    public function setProcessingDates(ProcessingDates $processingDates): void
    {
        $this->processingDates = $processingDates;
    }


    public function getProcessingDates(): ProcessingDates
    {
        return $this->processingDates;
    }


    public function setSignature(Signature $signature): void
    {
        $this->signature = $signature;
    }


    public function getSignature(): Signature
    {
        return $this->signature;
    }
}
