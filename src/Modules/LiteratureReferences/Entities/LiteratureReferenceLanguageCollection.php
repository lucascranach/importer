<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities;

use CranachDigitalArchive\Importer\AbstractItemLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces\ILiteratureReference;

class LiteratureReferenceLanguageCollection extends AbstractItemLanguageCollection implements ILiteratureReference
{
    protected function createItem(): ILiteratureReference
    {
        return new LiteratureReference();
    }

    public function getId(): string
    {
        return $this->getReferenceId();
    }


    public function setMetadata(Metadata $metadata)
    {
        foreach ($this as $literatureReference) {
            $literatureReference->metadata = $metadata;
        }
    }


    public function getMetadata(): ?Metadata
    {
        return $this->first()->metadata;
    }


    public function setReferenceId(string $referenceId): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setReferenceId($referenceId);
        }
    }


    public function getReferenceId(): string
    {
        return $this->first()->referenceId;
    }


    public function setReferenceNumber(string $referenceNumber): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setReferenceNumber($referenceNumber);
        }
    }


    public function getReferenceNumber(): string
    {
        return $this->first()->referenceNumber;
    }


    public function setIsPrimarySource(bool $isPrimarySource)
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setIsPrimarySource($isPrimarySource);
        }
    }


    public function getPrimarySource(): bool
    {
        return $this->first()->isPrimarySource;
    }


    public function setTitle(string $title): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setTitle($title);
        }
    }


    public function getTitle(): string
    {
        return $this->first()->title;
    }


    public function setSubtitle(string $subtitle): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setSubtitle($subtitle);
        }
    }


    public function getSubtitle(): string
    {
        return $this->first()->subtitle;
    }


    public function setShortTitle(string $shortTitle): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setShortTitle($shortTitle);
        }
    }


    public function getShortTitle(): string
    {
        return $this->first()->shortTitle;
    }


    public function setLongTitle(string $longTitle): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setLongTitle($longTitle);
        }
    }


    public function getLongTitle(): string
    {
        return $this->first()->longTitle;
    }


    public function setJournal(string $journal): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setJournal($journal);
        }
    }


    public function getJournal(): string
    {
        return $this->first()->journal;
    }


    public function setSeries(string $series): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setSeries($series);
        }
    }


    public function getSeries(): string
    {
        return $this->first()->series;
    }


    public function setVolume(string $volume): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setVolume($volume);
        }
    }


    public function getVolume(): string
    {
        return $this->first()->volume;
    }


    public function setEdition(string $edition): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setEdition($edition);
        }
    }


    public function getEdition(): string
    {
        return $this->first()->edition;
    }


    public function setPublishLocation(string $publishLocation): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setPublishLocation($publishLocation);
        }
    }


    public function getPublishLocation(): string
    {
        return $this->first()->publishLocation;
    }


    public function setPublishDate(string $publishDate): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setPublishDate($publishDate);
        }
    }


    public function getPublishDate(): string
    {
        return $this->first()->publishDate;
    }


    public function setPageNumbers(string $pageNumbers): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setPageNumbers($pageNumbers);
        }
    }


    public function getPageNumbers(): string
    {
        return $this->first()->pageNumbers;
    }


    public function setDate(string $date): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setDate($date);
        }
    }


    public function getDate(): string
    {
        return $this->first()->date;
    }

    public function addEvent(Event $event): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->addEvent($event);
        }
    }


    public function getEvents(): array
    {
        return $this->first()->events;
    }


    public function setCopyright(string $copyright): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setCopyright($copyright);
        }
    }


    public function getCopyright(): string
    {
        return $this->first()->copyright;
    }


    public function addPerson(Person $person): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->addPerson($person);
        }
    }


    public function getPersons(): array
    {
        return $this->first()->persons;
    }


    public function setPublications(array $publications): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->publications = $publications;
        }
    }


    public function addPublication(Publication $publication): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->addPublication($publication);
        }
    }


    public function getPublications(): array
    {
        return $this->first()->publications;
    }


    public function addConnectedObject(ConnectedObject $connectedObject): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->addConnectedObject($connectedObject);
        }
    }


    public function getConnectedObjects(): array
    {
        return $this->first()->connectedObjects;
    }


    public function addAlternateNumber(AlternateNumber $alternateNumber): void
    {
        foreach ($this as $literatureReference) {
            $literatureReference->addAlternateNumber($alternateNumber);
        }
    }


    public function getAlternateNumbers(): array
    {
        return $this->first()->alternateNumbers;
    }


    public function setPhysicalDescription(string $physicalDescription)
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setPhysicalDescription($physicalDescription);
        }
    }


    public function getPhysicalDescription(): string
    {
        return $this->first()->physicalDescription;
    }


    public function setMention(string $mention)
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setMention($mention);
        }
    }


    public function getMention(): string
    {
        return $this->first()->mention;
    }


    public function setAuthors(string $authors)
    {
        foreach ($this as $literatureReference) {
            $literatureReference->setAuthors($authors);
        }
    }


    public function getAuthors(): string
    {
        return $this->first()->authors;
    }
}
