<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Entities;

use CranachDigitalArchive\Importer\AbstractItemLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Publication;
use CranachDigitalArchive\Importer\Modules\Archivals\Interfaces\IArchival;

class ArchivalLanguageCollection extends AbstractItemLanguageCollection implements IArchival
{
    protected function createItem(): IArchival
    {
        return new Archival();
    }


    public function getId(): string
    {
        return $this->first()->getId();
    }

    public function getRemoteId(): string
    {
        return $this->first()->getRemoteId();
    }

    public function setMetadata(Metadata $metadata)
    {
        foreach ($this as $archival) {
            $archival->setMetadata($metadata);
        }
    }

    public function getMetadata(): ?Metadata
    {
        return $this->first()->getMetadata();
    }

    public function setInventoryNumber(string $inventoryNumber): void
    {
        foreach ($this as $archival) {
            $archival->setInventoryNumber($inventoryNumber);
        }
    }

    public function getInventoryNumber(): string
    {
        return $this->first()->getInventoryNumber();
    }

    public function setDating(Dating $dating): void
    {
        foreach ($this as $archival) {
            $archival->setDating($dating);
        }
    }

    public function getDating(): ?Dating
    {
        return $this->first()->getDating();
    }

    public function addSummary(string $summary): void
    {
        foreach ($this as $archival) {
            $archival->addSummary($summary);
        }
    }

    public function getSummaries(): array
    {
        return $this->first()->getSummaries();
    }

    public function setTranscription(string $transcription): void
    {
        foreach ($this as $archival) {
            $archival->setTranscription($transcription);
        }
    }

    public function getTranscription(): string
    {
        return $this->first()->getTranscription();
    }

    public function setLocationAndDate(string $locationAndDate): void
    {
        foreach ($this as $archival) {
            $archival->setLocationAndDate($locationAndDate);
        }
    }

    public function getLocationAndDate(): string
    {
        return $this->first()->getLocationAndDate();
    }

    public function setRepository(string $repository): void
    {
        foreach ($this as $archival) {
            $archival->setRepository($repository);
        }
    }

    public function getRepository(): string
    {
        return $this->first()->getRepository();
    }

    public function setSignature(string $signature): void
    {
        foreach ($this as $archival) {
            $archival->setSignature($signature);
        }
    }

    public function getSignature(): string
    {
        return $this->first()->getSignature();
    }

    public function setComments(string $comments): void
    {
        foreach ($this as $archival) {
            $archival->setComments($comments);
        }
    }

    public function getComments(): string
    {
        return $this->first()->getComments();
    }

    public function setTranscribedBy(string $transcribedBy): void
    {
        foreach ($this as $archival) {
            $archival->setTranscribedBy($transcribedBy);
        }
    }

    public function getTranscribedBy(): string
    {
        return $this->first()->getTranscribedBy();
    }

    public function setTranscriptionDate(string $transcriptionDate): void
    {
        foreach ($this as $archival) {
            $archival->setTranscriptionDate($transcriptionDate);
        }
    }

    public function getTranscriptionDate(): string
    {
        return $this->first()->getTranscriptionDate();
    }

    public function setTranscribedAccordingTo(string $transcribedAccordingTo): void
    {
        foreach ($this as $archival) {
            $archival->setTranscribedAccordingTo($transcribedAccordingTo);
        }
    }

    public function getTranscribedAccordingTo(): string
    {
        return $this->first()->getTranscribedAccordingTo();
    }

    public function setVerification(string $verification): void
    {
        foreach ($this as $archival) {
            $archival->setVerification($verification);
        }
    }

    public function getVerification(): string
    {
        return $this->first()->getVerification();
    }

    public function setScans(string $scans): void
    {
        foreach ($this as $archival) {
            $archival->setScans($scans);
        }
    }

    public function getScans(): string
    {
        return $this->first()->getScans();
    }

    public function setDocumentReferences(array $documentReferences): void
    {
        foreach ($this as $archival) {
            $archival->setDocumentReferences($documentReferences);
        }
    }

    public function getDocumentReferences(): array
    {
        return $this->first()->getDocumentReferences();
    }

    public function setScanNames(array $scanNames): void
    {
        foreach ($this as $archival) {
            $archival->setScanNames($scanNames);
        }
    }

    public function getScanNames(): array
    {
        return $this->first()->getScanNames();
    }

    public function setPeriod(string $period): void
    {
        foreach ($this as $archival) {
            $archival->setPeriod($period);
        }
    }

    public function getPeriod(): string
    {
        return $this->first()->getPeriod();
    }

    public function addPublication(Publication $publication): void
    {
        foreach ($this as $archival) {
            $archival->addPublication($publication);
        }
    }

    public function getPublications(): array
    {
        return $this->first()->getPublications();
    }

    public function setImages(array $images): void
    {
        foreach ($this as $archival) {
            $archival->setImages($images);
        }
    }

    public function getImages()
    {
        $this->first()->getImages();
    }

    public function setDocuments(array $documents): void
    {
        foreach ($this as $archival) {
            $archival->setDocuments($documents);
        }
    }

    public function getDocuments()
    {
        $this->first()->getDocuments();
    }
}
