<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;

/**
 * Representing a single literature reference
 */
class LiteratureReference implements IBaseItem
{
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
    public $id = ''; /* ? */

    public $connectedObjects = [];


    public function __construct()
    {
    }


    public function setReferenceId(string $referenceId)
    {
        $this->referenceId = $referenceId;
    }


    public function getReferenceId(): string
    {
        return $this->referenceId;
    }


    public function setReferenceNumber(string $referenceNumber)
    {
        $this->referenceNumber = $referenceNumber;
    }


    public function getReferenceNumber(): string
    {
        return $this->referenceNumber;
    }


    public function setTitle(string $title)
    {
        $this->title = $title;
    }


    public function getTitle(): string
    {
        return $this->title;
    }


    public function setSubtitle(string $subtitle)
    {
        $this->subtitle = $subtitle;
    }


    public function getSubtitle(): string
    {
        return $this->subtitle;
    }


    public function setShorttitle(string $shorttitle)
    {
        $this->shorttitle = $shorttitle;
    }


    public function getShorttitle(): string
    {
        return $this->shorttitle;
    }


    public function setJournal(string $journal)
    {
        $this->journal = $journal;
    }


    public function getJournal(): string
    {
        return $this->journal;
    }


    public function setSeries(string $series)
    {
        $this->series = $series;
    }


    public function getSeries(): string
    {
        return $this->series;
    }


    public function setVolume(string $volume)
    {
        $this->volume = $volume;
    }


    public function getVolume(): string
    {
        return $this->volume;
    }


    public function setEdition(string $edition)
    {
        $this->edition = $edition;
    }


    public function getEdition(): string
    {
        return $this->edition;
    }


    public function setPublishLocation(string $publishLocation)
    {
        $this->publishLocation = $publishLocation;
    }


    public function getPublishLocation(): string
    {
        return $this->publishLocation;
    }


    public function setPublishDate(string $publishDate)
    {
        $this->publishDate = $publishDate;
    }


    public function getPublishDate(): string
    {
        return $this->publishDate;
    }


    public function setPageNumbers(string $pageNumbers)
    {
        $this->pageNumbers = $pageNumbers;
    }


    public function getPageNumbers(): string
    {
        return $this->pageNumbers;
    }


    public function setDate(string $date)
    {
        $this->date = $date;
    }


    public function getDate(): string
    {
        return $this->date;
    }

    public function addEvent(Event $event)
    {
        $this->events[] = $event;
    }


    public function getEvents(): array
    {
        return $this->events;
    }


    public function setCopyright(string $copyright)
    {
        $this->copyright = $copyright;
    }


    public function getCopyright(): string
    {
        return $this->copyright;
    }


    public function addPerson(Person $person)
    {
        $this->persons[] = $person;
    }


    public function getPersons(): array
    {
        return $this->persons;
    }


    public function addPublication(Publication $publication)
    {
        $this->publications[] = $publication;
    }


    public function getPublications(): array
    {
        return $this->publications;
    }


    public function setId(string $id)
    {
        $this->id = $id;
    }


    public function getId(): array
    {
        return $this->id;
    }


    public function addConnectedObject(ConnectedObject $connectedObject)
    {
        $this->connectedObjects[] = $connectedObject;
    }


    public function getConnectedObjects(): array
    {
        return $this->connectedObjects;
    }
}
