<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Event;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Publication;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Person;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\ConnectedObject;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\AlternateNumber;

interface ILiteratureReference extends IBaseItem
{
    public function setMetadata(Metadata $metadata);

    public function getMetadata(): ?Metadata;

    public function setReferenceId(string $referenceId): void;

    public function getReferenceId(): string;

    public function setReferenceNumber(string $referenceNumber): void;

    public function getReferenceNumber(): string;

    public function setIsPrimarySource(bool $isPrimarySource);

    public function getPrimarySource(): bool;

    public function setTitle(string $title): void;

    public function getTitle(): string;

    public function setSubtitle(string $subtitle): void;

    public function getSubtitle(): string;

    public function setShortTitle(string $shortTitle): void;

    public function getShortTitle(): string;

    public function setLongTitle(string $longTitle): void;

    public function getLongTitle(): string;

    public function setJournal(string $journal): void;

    public function getJournal(): string;

    public function setSeries(string $series): void;

    public function getSeries(): string;

    public function setVolume(string $volume): void;

    public function getVolume(): string;

    public function setEdition(string $edition): void;

    public function getEdition(): string;

    public function setPublishLocation(string $publishLocation): void;

    public function getPublishLocation(): string;

    public function setPublishDate(string $publishDate): void;

    public function getPublishDate(): string;

    public function setPageNumbers(string $pageNumbers): void;

    public function getPageNumbers(): string;

    public function setDate(string $date): void;

    public function getDate(): string;

    public function addEvent(Event $event): void;

    public function getEvents(): array;

    public function setCopyright(string $copyright): void;

    public function getCopyright(): string;

    public function addPerson(Person $person): void;

    public function getPersons(): array;

    public function setPublications(array $publications): void;

    public function addPublication(Publication $publication): void;

    public function getPublications(): array;

    public function addConnectedObject(ConnectedObject $connectedObject): void;

    public function getConnectedObjects(): array;

    public function addAlternateNumber(AlternateNumber $alternateNumber): void;

    public function getAlternateNumbers(): array;

    public function setPhysicalDescription(string $physicalDescription);

    public function getPhysicalDescription(): string;

    public function setMention(string $mention);

    public function getMention(): string;

    public function setAuthors(string $authors);

    public function getAuthors(): string;
}
