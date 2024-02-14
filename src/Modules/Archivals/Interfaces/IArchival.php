<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Interfaces;

use CranachDigitalArchive\Importer\Interfaces\Entities\IImagesItem;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Publication;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\Dating;

interface IArchival extends IImagesItem
{
    public function getId(): string;

    public function getRemoteId(): string;

    public function setMetadata(Metadata $metadata);

    public function getMetadata(): ?Metadata;

    public function setInventoryNumber(string $inventoryNumber): void;

    public function getInventoryNumber(): string;

    public function setDating(Dating $dating): void;

    public function getDating(): ?Dating;

    public function addSummary(string $summary): void;

    public function getSummaries(): array;

    public function setTranscription(string $transcription): void;

    public function getTranscription(): string;

    public function setLocationAndDate(string $locationAndDate): void;

    public function getLocationAndDate(): string;

    public function setRepository(string $repository): void;

    public function getRepository(): string;

    public function setSignature(string $signature): void;

    public function getSignature(): string;

    public function setComments(string $comments): void;

    public function getComments(): string;

    public function setTranscribedBy(string $transcribedBy): void;

    public function getTranscribedBy(): string;

    public function setTranscriptionDate(string $transcriptionDate): void;

    public function getTranscriptionDate(): string;

    public function setTranscribedAccordingTo(string $transcribedAccordingTo): void;

    public function getTranscribedAccordingTo(): string;

    public function setVerification(string $verification): void;

    public function getVerification(): string;

    public function setScans(string $scans): void;

    public function getScans(): string;

    public function setDocumentReferences(array$documentReferences): void;

    public function getDocumentReferences(): array;

    public function setScanNames(array $scanNames): void;

    public function getScanNames(): array;

    public function setPeriod(string $period): void;

    public function getPeriod(): string;

    public function addPublication(Publication $publication): void;

    public function getPublications(): array;

    public function setSortingNumber(string $sortingNumber): void;

    public function getSortingNumber(): string;

    public function setSearchSortingNumber(string $searchSortingNumber): void;

    public function getSearchSortingNumber(): string;
}
