<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;

use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces\ILiteratureReference;

/**
 * Representing a single literature reference
 */
class LiteratureReference implements ILiteratureReference
{
    const ENTITY_TYPE = 'LITERATURE_REFERENCE';

    public $metadata = null;
    public $referenceId = '';
    public $referenceNumber = '';
    public $isPrimarySource = false;
    public $title = '';
    public $subtitle = '';
    public $shortTitle = '';
    public $longTitle = '';
    public $journal = '';
    public $series = '';
    public $volume = '';
    public $edition = '';
    public $publishLocation = '';
    public $publishDate = '';
    public $pageNumbers = '';
    public $date = '';
    public $events = [];
    public $copyright = '';
    public $persons = [];
    public $publications = [];
    public $alternateNumbers = [];
    public $physicalDescription = '';
    public $mention = '';

    public $authors = '';

    public $connectedObjects = [];


    public function __construct()
    {
    }


    public function getId(): string
    {
        return $this->getReferenceId();
    }


    public function setMetadata(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }


    public function getMetadata(): ?Metadata
    {
        return $this->metadata;
    }


    public function setReferenceId(string $referenceId): void
    {
        $this->referenceId = $referenceId;
    }


    public function getReferenceId(): string
    {
        return $this->referenceId;
    }


    public function setReferenceNumber(string $referenceNumber): void
    {
        $this->referenceNumber = $referenceNumber;
    }


    public function getReferenceNumber(): string
    {
        return $this->referenceNumber;
    }


    public function setIsPrimarySource(bool $isPrimarySource)
    {
        $this->isPrimarySource = $isPrimarySource;
    }


    public function getPrimarySource(): bool
    {
        return $this->isPrimarySource;
    }


    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    public function getTitle(): string
    {
        return $this->title;
    }


    public function setSubtitle(string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }


    public function getSubtitle(): string
    {
        return $this->subtitle;
    }


    public function setShortTitle(string $shortTitle): void
    {
        $this->shortTitle = $shortTitle;
    }


    public function getShortTitle(): string
    {
        return $this->shortTitle;
    }


    public function setLongTitle(string $longTitle): void
    {
        $this->longTitle = $longTitle;
    }


    public function getLongTitle(): string
    {
        return $this->longTitle;
    }


    public function setJournal(string $journal): void
    {
        $this->journal = $journal;
    }


    public function getJournal(): string
    {
        return $this->journal;
    }


    public function setSeries(string $series): void
    {
        $this->series = $series;
    }


    public function getSeries(): string
    {
        return $this->series;
    }


    public function setVolume(string $volume): void
    {
        $this->volume = $volume;
    }


    public function getVolume(): string
    {
        return $this->volume;
    }


    public function setEdition(string $edition): void
    {
        $this->edition = $edition;
    }


    public function getEdition(): string
    {
        return $this->edition;
    }


    public function setPublishLocation(string $publishLocation): void
    {
        $this->publishLocation = $publishLocation;
    }


    public function getPublishLocation(): string
    {
        return $this->publishLocation;
    }


    public function setPublishDate(string $publishDate): void
    {
        $this->publishDate = $publishDate;
    }


    public function getPublishDate(): string
    {
        return $this->publishDate;
    }


    public function setPageNumbers(string $pageNumbers): void
    {
        $this->pageNumbers = $pageNumbers;
    }


    public function getPageNumbers(): string
    {
        return $this->pageNumbers;
    }


    public function setDate(string $date): void
    {
        $this->date = $date;
    }


    public function getDate(): string
    {
        return $this->date;
    }

    public function addEvent(Event $event): void
    {
        $this->events[] = $event;
    }


    public function getEvents(): array
    {
        return $this->events;
    }


    public function setCopyright(string $copyright): void
    {
        $this->copyright = $copyright;
    }


    public function getCopyright(): string
    {
        return $this->copyright;
    }


    public function addPerson(Person $person): void
    {
        $this->persons[] = $person;
    }


    public function getPersons(): array
    {
        return $this->persons;
    }


    public function setPublications(array $publications): void
    {
        $this->publications = $publications;
    }


    public function addPublication(Publication $publication): void
    {
        $this->publications[] = $publication;
    }


    public function getPublications(): array
    {
        return $this->publications;
    }


    public function addConnectedObject(ConnectedObject $connectedObject): void
    {
        $this->connectedObjects[] = $connectedObject;
    }


    public function getConnectedObjects(): array
    {
        return $this->connectedObjects;
    }


    public function addAlternateNumber(AlternateNumber $alternateNumber): void
    {
        $this->alternateNumbers[] = $alternateNumber;
    }


    public function getAlternateNumbers(): array
    {
        return $this->alternateNumbers;
    }


    public function setPhysicalDescription(string $physicalDescription)
    {
        $this->physicalDescription = $physicalDescription;
    }


    public function getPhysicalDescription(): string
    {
        return $this->physicalDescription;
    }


    public function setMention(string $mention)
    {
        $this->mention = $mention;
    }


    public function getMention(): string
    {
        return $this->mention;
    }


    public function setAuthors(string $authors)
    {
        $this->authors = $authors;
    }


    public function getAuthors(): string
    {
        return $this->authors;
    }
}
