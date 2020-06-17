<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Entities;

use CranachDigitalArchive\Importer\Interfaces\Entities\ILanguageBaseItem;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Publication;

/**
 * Representing a single archival and all its data
 */
class Archival implements ILanguageBaseItem
{
    public $langCode = '<unknown language>';

    public $inventoryNumber = '';
    public $dating = null;
    public $summaries = [];
    public $transcription = '';
    public $locationAndDate = '';
    public $repository = '';
    public $signature = '';
    public $comments = '';
    public $transcribedBy = '';
    public $transcriptionDate = '';
    public $transcribedAccordingTo = '';
    public $verification = '';
    public $scans = '';
    public $documents = '';
    public $scanNames = [];
    public $period = '';
    public $publications = [];


    public function __construct()
    {
    }

    public function setLangCode(string $langCode)
    {
        $this->langCode = $langCode;
    }


    public function getLangCode(): string
    {
        return $this->langCode;
    }


    public function setInventoryNumber(string $inventoryNumber)
    {
        $this->inventoryNumber = $inventoryNumber;
    }


    public function getInventoryNumber(): string
    {
        return $this->inventoryNumber;
    }


    public function setDating(Dating $dating)
    {
        $this->dating = $dating;
    }


    public function getDating(): ?Dating
    {
        return $this->dating;
    }


    public function addSummary(string $summary)
    {
        $this->summaries[] = $summary;
    }


    public function getSummaries(): array
    {
        return $this->summaries;
    }


    public function setTranscription(string $transcription)
    {
        $this->transcription = $transcription;
    }


    public function getTranscription(): string
    {
        return $this->transcription;
    }


    public function setLocationAndDate(string $locationAndDate)
    {
        $this->locationAndDate = $locationAndDate;
    }


    public function getLocationAndDate(): string
    {
        return $this->locationAndDate;
    }


    public function setRepository(string $repository)
    {
        $this->repository = $repository;
    }


    public function getRepository(): string
    {
        return $this->repository;
    }


    public function setSignature(string $signature)
    {
        $this->signature = $signature;
    }


    public function getSignature(): string
    {
        return $this->signature;
    }


    public function setComments(string $comments)
    {
        $this->comments = $comments;
    }


    public function getComments(): string
    {
        return $this->comments;
    }


    public function setTranscribedBy(string $transcribedBy)
    {
        $this->transcribedBy = $transcribedBy;
    }


    public function getTranscribedBy(): string
    {
        return $this->transcriptionDate;
    }


    public function setTranscriptionDate(string $transcriptionDate)
    {
        $this->transcriptionDate = $transcriptionDate;
    }


    public function getTranscriptionDate(): string
    {
        return $this->transcriptionDate;
    }


    public function setTranscribedAccordingTo(string $transcribedAccordingTo)
    {
        $this->transcribedAccordingTo = $transcribedAccordingTo;
    }


    public function getTranscribedAccordingTo(): string
    {
        return $this->transcribedAccordingTo;
    }


    public function setVerification(string $verification)
    {
        $this->verification = $verification;
    }


    public function getVerification(): string
    {
        return $this->verification;
    }


    public function setScans(string $scans)
    {
        $this->scans = $scans;
    }


    public function getScans(): string
    {
        return $this->scans;
    }


    public function setDocuments(string $documents)
    {
        $this->documents = $documents;
    }


    public function getDocuments(): string
    {
        return $this->documents;
    }


    public function setScanNames(array $scanNames)
    {
        $this->scanNames = $scanNames;
    }


    public function getScanNames(): array
    {
        return $this->scanNames;
    }


    public function setPeriod(string $period)
    {
        $this->period = $period;
    }


    public function getPeriod(): string
    {
        return $this->period;
    }


    public function addPublication(Publication $publication)
    {
        $this->publications[] = $publication;
    }


    public function getPublications(): array
    {
        return $this->publications;
    }
}
