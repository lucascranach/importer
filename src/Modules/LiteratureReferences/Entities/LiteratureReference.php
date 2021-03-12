<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;

/**
 * Representing a single literature reference
 */
class LiteratureReference implements IBaseItem
{
    public $entityType = 'LITERATURE_REFERENCE';
    public $referenceId = '';
    public $referenceNumber = '';
    public $title = '';
    public $subtitle = '';
    public $shorttitle = '';
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

    public $connectedObjects = [];


    public function __construct()
    {
    }


    public function getId(): string
    {
        return $this->getReferenceId();
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


    public function setShorttitle(string $shorttitle): void
    {
        $this->shorttitle = $shorttitle;
    }


    public function getShorttitle(): string
    {
        return $this->shorttitle;
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
}
