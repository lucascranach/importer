<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Entities;

use CranachDigitalArchive\Importer\Modules\Main\Entities\AbstractImagesItem;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Publication;
use CranachDigitalArchive\Importer\Modules\Archivals\Interfaces\IArchival;

/**
 * Representing a single archival and all its data
 */
class Archival extends AbstractImagesItem implements IArchival
{
    const ENTITY_TYPE = 'ARCHIVAL';

    public $metadata = null;
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
    public $documentReferences = [];
    public $scanNames = [];
    public $period = '';
    public $publications = [];


    public function __construct()
    {
    }


    public function getId(): string
    {
        return $this->getInventoryNumber();
    }


    public function getRemoteId(): string
    {
        return $this->getInventoryNumber();
    }


    public function setMetadata(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }


    public function getMetadata(): ?Metadata
    {
        return $this->metadata;
    }


    public function setInventoryNumber(string $inventoryNumber): void
    {
        $this->inventoryNumber = $inventoryNumber;
    }


    public function getInventoryNumber(): string
    {
        return $this->inventoryNumber;
    }


    public function setDating(Dating $dating): void
    {
        $this->dating = $dating;
    }


    public function getDating(): ?Dating
    {
        return $this->dating;
    }


    public function addSummary(string $summary): void
    {
        $this->summaries[] = $summary;
    }


    public function getSummaries(): array
    {
        return $this->summaries;
    }


    public function setTranscription(string $transcription): void
    {
        $this->transcription = $transcription;
    }


    public function getTranscription(): string
    {
        return $this->transcription;
    }


    public function setLocationAndDate(string $locationAndDate): void
    {
        $this->locationAndDate = $locationAndDate;
    }


    public function getLocationAndDate(): string
    {
        return $this->locationAndDate;
    }


    public function setRepository(string $repository): void
    {
        $this->repository = $repository;
    }


    public function getRepository(): string
    {
        return $this->repository;
    }


    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
    }


    public function getSignature(): string
    {
        return $this->signature;
    }


    public function setComments(string $comments): void
    {
        $this->comments = $comments;
    }


    public function getComments(): string
    {
        return $this->comments;
    }


    public function setTranscribedBy(string $transcribedBy): void
    {
        $this->transcribedBy = $transcribedBy;
    }


    public function getTranscribedBy(): string
    {
        return $this->transcriptionDate;
    }


    public function setTranscriptionDate(string $transcriptionDate): void
    {
        $this->transcriptionDate = $transcriptionDate;
    }


    public function getTranscriptionDate(): string
    {
        return $this->transcriptionDate;
    }


    public function setTranscribedAccordingTo(string $transcribedAccordingTo): void
    {
        $this->transcribedAccordingTo = $transcribedAccordingTo;
    }


    public function getTranscribedAccordingTo(): string
    {
        return $this->transcribedAccordingTo;
    }


    public function setVerification(string $verification): void
    {
        $this->verification = $verification;
    }


    public function getVerification(): string
    {
        return $this->verification;
    }


    public function setScans(string $scans): void
    {
        $this->scans = $scans;
    }


    public function getScans(): string
    {
        return $this->scans;
    }


    public function setDocumentReferences(array$documentReferences): void
    {
        $this->documentReferences = $documentReferences;
    }


    public function getDocumentReferences(): array
    {
        return $this->documentReferences;
    }


    public function setScanNames(array $scanNames): void
    {
        $this->scanNames = $scanNames;
    }


    public function getScanNames(): array
    {
        return $this->scanNames;
    }


    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }


    public function getPeriod(): string
    {
        return $this->period;
    }


    public function addPublication(Publication $publication): void
    {
        $this->publications[] = $publication;
    }


    public function getPublications(): array
    {
        return $this->publications;
    }
}
