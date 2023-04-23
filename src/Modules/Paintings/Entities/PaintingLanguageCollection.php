<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Entities;

use CranachDigitalArchive\Importer\AbstractItemLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Person;
use CranachDigitalArchive\Importer\Modules\Main\Entities\PersonName;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Title;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Dating;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\AdditionalTextInformation;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Publication;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaLocationReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\CatalogWorkReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\StructuredDimension;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Classification;
use CranachDigitalArchive\Importer\Modules\Paintings\Interfaces\IPainting;

/**
 * @extends AbstractItemLanguageCollection<IPainting>
 */
class PaintingLanguageCollection extends AbstractItemLanguageCollection implements IPainting
{
    protected function createItem(): IPainting
    {
        return new Painting();
    }


    public function getRemoteId(): string
    {
        return $this->first()->getRemoteId();
    }


    public function getId(): string
    {
        return $this->first()->getId();
    }


    public function setMetadata(Metadata $metadata)
    {
        foreach ($this as $painting) {
            $painting->setMetadata($metadata);
        }
    }


    public function getMetadata(): ?Metadata
    {
        return $this->first()->getMetadata();
    }


    public function addPerson(Person $person): void
    {
        foreach ($this as $painting) {
            $painting->addPerson($person);
        }
    }


    public function getPersons(): array
    {
        return $this->first()->getPersons();
    }


    public function addPersonName(PersonName $personName): void
    {
        foreach ($this as $painting) {
            $painting->addPersonName($personName);
        }
    }


    public function getPersonNames(): array
    {
        return $this->first()->getPersonNames();
    }


    public function addTitle(Title $title): void
    {
        foreach ($this as $painting) {
            $painting->addTitle($title);
        }
    }


    public function getTitles(): array
    {
        return $this->first()->getTitles();
    }


    public function setClassification(Classification $classification): void
    {
        foreach ($this as $painting) {
            $painting->setClassification($classification);
        }
    }


    public function getClassification(): Classification
    {
        return $this->first()->getClassification();
    }


    public function setObjectName(string $objectName): void
    {
        foreach ($this as $painting) {
            $painting->setObjectName($objectName);
        }
    }


    public function getObjectName(): string
    {
        return $this->first()->getObjectName();
    }


    public function setInventoryNumber(string $inventoryNumber): void
    {
        foreach ($this as $painting) {
            $painting->setInventoryNumber($inventoryNumber);
        }
    }


    public function getInventoryNumber(): string
    {
        return $this->first()->getInventoryNumber();
    }


    public function setObjectId(int $objectId): void
    {
        foreach ($this as $painting) {
            $painting->setObjectId($objectId);
        }
    }


    public function getObjectId(): int
    {
        return $this->first()->getObjectId();
    }


    public function setDimensions(string $dimensions): void
    {
        foreach ($this as $painting) {
            $painting->setDimensions($dimensions);
        }
    }


    public function getDimensions(): string
    {
        return $this->first()->getDimensions();
    }


    public function setDating(Dating $dating): void
    {
        foreach ($this as $painting) {
            $painting->setDating($dating);
        }
    }


    public function getDating(): ?Dating
    {
        return $this->first()->getDating();
    }


    public function setDescription(string $description): void
    {
        foreach ($this as $painting) {
            $painting->setDescription($description);
        }
    }


    public function getDescription(): string
    {
        return $this->first()->getDescription();
    }


    public function setProvenance(string $provenance): void
    {
        foreach ($this as $painting) {
            $painting->setProvenance($provenance);
        }
    }


    public function getProvenance(): string
    {
        return $this->first()->getProvenance();
    }


    public function setMedium(string $medium): void
    {
        foreach ($this as $painting) {
            $painting->setMedium($medium);
        }
    }


    public function getMedium(): string
    {
        return $this->first()->getMedium();
    }


    public function setSignature(string $signature): void
    {
        foreach ($this as $painting) {
            $painting->setSignature($signature);
        }
    }


    public function getSignature(): string
    {
        return $this->first()->getSignature();
    }


    public function setInscription(string $inscription): void
    {
        foreach ($this as $painting) {
            $painting->setInscription($inscription);
        }
    }


    public function getInscription(): string
    {
        return $this->first()->getInscription();
    }


    public function setMarkings(string $markings): void
    {
        foreach ($this as $painting) {
            $painting->setMarkings($markings);
        }
    }


    public function getMarkings(): string
    {
        return $this->first()->getMarkings();
    }


    public function setRelatedWorks(string $relatedWorks): void
    {
        foreach ($this as $painting) {
            $painting->setRelatedWorks($relatedWorks);
        }
    }


    public function getRelatedWorks(): string
    {
        return $this->first()->getRelatedWorks();
    }


    public function setExhibitionHistory(string $exhibitionHistory): void
    {
        foreach ($this as $painting) {
            $painting->setExhibitionHistory($exhibitionHistory);
        }
    }


    public function getExhibitionHistory(): string
    {
        return $this->first()->getExhibitionHistory();
    }


    public function setBibliography(string $bibliography): void
    {
        foreach ($this as $painting) {
            $painting->setBibliography($bibliography);
        }
    }


    public function getBibliography(): string
    {
        return $this->first()->getBibliography();
    }


    public function setReferences(array $references): void
    {
        foreach ($this as $painting) {
            $painting->setReferences($references);
        }
    }


    public function addReference(ObjectReference $reference): void
    {
        foreach ($this as $painting) {
            $painting->addReference($reference);
        }
    }


    public function getReferences(): array
    {
        return $this->first()->getReferences();
    }


    public function addSecondaryReference(ObjectReference $reference): void
    {
        foreach ($this as $painting) {
            $painting->addSecondaryReference($reference);
        }
    }


    public function getSecondaryReferences(): array
    {
        return $this->first()->getSecondaryReferences();
    }


    public function addAdditionalTextInformation(AdditionalTextInformation $additonalTextInformation): void
    {
        foreach ($this as $painting) {
            $painting->addAdditionalTextInformation($additonalTextInformation);
        }
    }


    public function getAdditionalTextInformations(): array
    {
        return $this->first()->getAdditionalTextInformations();
    }


    public function addPublication(Publication $publication): void
    {
        foreach ($this as $painting) {
            $painting->addPublication($publication);
        }
    }


    public function getPublications(): array
    {
        return $this->first()->getPublications();
    }


    public function addKeyword(MetaReference $keyword): void
    {
        foreach ($this as $painting) {
            $painting->addKeyword($keyword);
        }
    }


    public function getKeywords(): array
    {
        return $this->first()->getKeywords();
    }


    public function setLocations(array $locations): void
    {
        foreach ($this as $painting) {
            $painting->setLocations($locations);
        }
    }


    public function getLocations(): array
    {
        return $this->first()->getLocations();
    }


    public function addLocation(MetaLocationReference $location): void
    {
        foreach ($this as $painting) {
            $painting->addLocation($location);
        }
    }


    public function setRepository(string $repository): void
    {
        foreach ($this as $painting) {
            $painting->setRepository($repository);
        }
    }


    public function getRepository(): string
    {
        return $this->first()->getRepository();
    }


    public function setOwner(string $owner): void
    {
        foreach ($this as $painting) {
            $painting->setOwner($owner);
        }
    }


    public function getOwner(): string
    {
        return $this->first()->getOwner();
    }


    public function setCollectionRepositoryId(string $id): void
    {
        foreach ($this as $painting) {
            $painting->setCollectionRepositoryId($id);
        }
    }


    public function getCollectionId(): string
    {
        return $this->first()->getCollectionId();
    }


    public function setSortingNumber(string $sortingNumber): void
    {
        foreach ($this as $painting) {
            $painting->setSortingNumber($sortingNumber);
        }
    }


    public function getSortingNumber(): string
    {
        return $this->first()->getSortingNumber();
    }


    public function addCatalogWorkReference(CatalogWorkReference $catalogWorkReference): void
    {
        foreach ($this as $painting) {
            $painting->addCatalogWorkReference($catalogWorkReference);
        }
    }


    public function getCatalogWorkReferences(): array
    {
        return $this->first()->getCatalogWorkReferences();
    }


    public function setStructuredDimension(StructuredDimension $structuredDimension): void
    {
        foreach ($this as $painting) {
            $painting->setStructuredDimension($structuredDimension);
        }
    }


    public function getStructuredDimension(): StructuredDimension
    {
        return $this->first()->getStructuredDimension();
    }


    public function setIsBestOf(bool $isBestOf): void
    {
        foreach ($this as $painting) {
            $painting->setIsBestOf($isBestOf);
        }
    }


    public function getIsBestOf(): bool
    {
        return $this->first()->getIsBestOf();
    }


    public function setRestorationSurveys(array $restorationSurveys): void
    {
        foreach ($this as $painting) {
            $painting->setRestorationSurveys($restorationSurveys);
        }
    }


    public function getRestorationSurveys(): array
    {
        return $this->first()->getRestorationSurveys();
    }


    public function setSearchSortingNumber(string $searchSortingNumber): void
    {
        foreach ($this as $painting) {
            $painting->setSearchSortingNumber($searchSortingNumber);
        }
    }


    public function getSearchSortingNumber(): string
    {
        return $this->first()->getSearchSortingNumber();
    }


    public function setImages(array $images): void
    {
        foreach ($this as $painting) {
            $painting->setImages($images);
        }
    }


    public function getImages()
    {
        return $this->first()->getImages();
    }


    public function setDocuments(array $documents): void
    {
        foreach ($this as $painting) {
            $painting->setDocuments($documents);
        }
    }


    public function getDocuments()
    {
        return $this->first()->getDocuments();
    }
}
