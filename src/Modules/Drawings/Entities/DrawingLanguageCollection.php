<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Entities;

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
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\Classification;
use CranachDigitalArchive\Importer\Modules\Drawings\Interfaces\IDrawing;

/**
 * @extends AbstractItemLanguageCollection<IDrawing>
 */
class DrawingLanguageCollection extends AbstractItemLanguageCollection implements IDrawing
{
    protected function createItem(): IDrawing
    {
        return new Drawing();
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
        foreach ($this as $drawing) {
            $drawing->setMetadata($metadata);
        }
    }


    public function getMetadata(): ?Metadata
    {
        return $this->first()->getMetadata();
    }


    public function addPerson(Person $person): void
    {
        foreach ($this as $drawing) {
            $drawing->addPerson($person);
        }
    }


    public function getPersons(): array
    {
        return $this->first()->getPersons();
    }


    public function addPersonName(PersonName $personName): void
    {
        foreach ($this as $drawing) {
            $drawing->addPersonName($personName);
        }
    }


    public function getPersonNames(): array
    {
        return $this->first()->getPersonNames();
    }


    public function addTitle(Title $title): void
    {
        foreach ($this as $drawing) {
            $drawing->addTitle($title);
        }
    }


    public function getTitles(): array
    {
        return $this->first()->getTitles();
    }


    public function setClassification(Classification $classification): void
    {
        foreach ($this as $drawing) {
            $drawing->setClassification($classification);
        }
    }


    public function getClassification(): Classification
    {
        return $this->first()->getClassification();
    }


    public function setObjectName(string $objectName): void
    {
        foreach ($this as $drawing) {
            $drawing->setObjectName($objectName);
        }
    }


    public function getObjectName(): string
    {
        return $this->first()->getObjectName();
    }


    public function setInventoryNumber(string $inventoryNumber): void
    {
        foreach ($this as $drawing) {
            $drawing->setInventoryNumber($inventoryNumber);
        }
    }


    public function getInventoryNumber(): string
    {
        return $this->first()->getInventoryNumber();
    }


    public function setObjectId(int $objectId): void
    {
        foreach ($this as $drawing) {
            $drawing->setObjectId($objectId);
        }
    }


    public function getObjectId(): int
    {
        return $this->first()->getObjectId();
    }


    public function setDimensions(string $dimensions): void
    {
        foreach ($this as $drawing) {
            $drawing->setDimensions($dimensions);
        }
    }


    public function getDimensions(): string
    {
        return $this->first()->getDimensions();
    }


    public function setDating(Dating $dating): void
    {
        foreach ($this as $drawing) {
            $drawing->setDating($dating);
        }
    }


    public function getDating(): ?Dating
    {
        return $this->first()->getDating();
    }


    public function setDescription(string $description): void
    {
        foreach ($this as $drawing) {
            $drawing->setDescription($description);
        }
    }


    public function getDescription(): string
    {
        return $this->first()->getDescription();
    }


    public function setProvenance(string $provenance): void
    {
        foreach ($this as $drawing) {
            $drawing->setProvenance($provenance);
        }
    }


    public function getProvenance(): string
    {
        return $this->first()->getProvenance();
    }


    public function setMedium(string $medium): void
    {
        foreach ($this as $drawing) {
            $drawing->setMedium($medium);
        }
    }


    public function getMedium(): string
    {
        return $this->first()->getMedium();
    }


    public function setSignature(string $signature): void
    {
        foreach ($this as $drawing) {
            $drawing->setSignature($signature);
        }
    }


    public function getSignature(): string
    {
        return $this->first()->getSignature();
    }


    public function setInscription(string $inscription): void
    {
        foreach ($this as $drawing) {
            $drawing->setInscription($inscription);
        }
    }


    public function getInscription(): string
    {
        return $this->first()->getInscription();
    }


    public function setMarkings(string $markings): void
    {
        foreach ($this as $drawing) {
            $drawing->setMarkings($markings);
        }
    }


    public function getMarkings(): string
    {
        return $this->first()->getMarkings();
    }


    public function setRelatedWorks(string $relatedWorks): void
    {
        foreach ($this as $drawing) {
            $drawing->setRelatedWorks($relatedWorks);
        }
    }


    public function getRelatedWorks(): string
    {
        return $this->first()->getRelatedWorks();
    }


    public function setExhibitionHistory(string $exhibitionHistory): void
    {
        foreach ($this as $drawing) {
            $drawing->setExhibitionHistory($exhibitionHistory);
        }
    }


    public function getExhibitionHistory(): string
    {
        return $this->first()->getExhibitionHistory();
    }


    public function setBibliography(string $bibliography): void
    {
        foreach ($this as $drawing) {
            $drawing->setBibliography($bibliography);
        }
    }


    public function getBibliography(): string
    {
        return $this->first()->getBibliography();
    }


    public function setReferences(array $references): void
    {
        foreach ($this as $drawing) {
            $drawing->setReferences($references);
        }
    }


    public function addReference(ObjectReference $reference): void
    {
        foreach ($this as $drawing) {
            $drawing->addReference($reference);
        }
    }


    public function getReferences(): array
    {
        return $this->first()->getReferences();
    }


    public function addSecondaryReference(ObjectReference $reference): void
    {
        foreach ($this as $drawing) {
            $drawing->addSecondaryReference($reference);
        }
    }


    public function getSecondaryReferences(): array
    {
        return $this->first()->getSecondaryReferences();
    }


    public function addAdditionalTextInformation(AdditionalTextInformation $additonalTextInformation): void
    {
        foreach ($this as $drawing) {
            $drawing->addAdditionalTextInformation($additonalTextInformation);
        }
    }


    public function getAdditionalTextInformations(): array
    {
        return $this->first()->getAdditionalTextInformations();
    }


    public function addPublication(Publication $publication): void
    {
        foreach ($this as $drawing) {
            $drawing->addPublication($publication);
        }
    }


    public function getPublications(): array
    {
        return $this->first()->getPublications();
    }


    public function addKeyword(MetaReference $keyword): void
    {
        foreach ($this as $drawing) {
            $drawing->addKeyword($keyword);
        }
    }


    public function getKeywords(): array
    {
        return $this->first()->getKeywords();
    }


    public function setLocations(array $locations): void
    {
        foreach ($this as $drawing) {
            $drawing->setLocations($locations);
        }
    }


    public function getLocations(): array
    {
        return $this->first()->getLocations();
    }


    public function addLocation(MetaLocationReference $location): void
    {
        foreach ($this as $drawing) {
            $drawing->addLocation($location);
        }
    }


    public function setRepository(string $repository): void
    {
        foreach ($this as $drawing) {
            $drawing->setRepository($repository);
        }
    }


    public function getRepository(): string
    {
        return $this->first()->getRepository();
    }


    public function setOwner(string $owner): void
    {
        foreach ($this as $drawing) {
            $drawing->setOwner($owner);
        }
    }


    public function getOwner(): string
    {
        return $this->first()->getOwner();
    }


    public function setCollectionRepositoryId(string $id): void
    {
        foreach ($this as $drawing) {
            $drawing->setCollectionRepositoryId($id);
        }
    }


    public function getCollectionId(): string
    {
        return $this->first()->getCollectionId();
    }


    public function setSortingNumber(string $sortingNumber): void
    {
        foreach ($this as $drawing) {
            $drawing->setSortingNumber($sortingNumber);
        }
    }


    public function getSortingNumber(): string
    {
        return $this->first()->getSortingNumber();
    }


    public function addCatalogWorkReference(CatalogWorkReference $catalogWorkReference): void
    {
        foreach ($this as $drawing) {
            $drawing->addCatalogWorkReference($catalogWorkReference);
        }
    }


    public function getCatalogWorkReferences(): array
    {
        return $this->first()->getCatalogWorkReferences();
    }


    public function setStructuredDimension(StructuredDimension $structuredDimension): void
    {
        foreach ($this as $drawing) {
            $drawing->setStructuredDimension($structuredDimension);
        }
    }


    public function getStructuredDimension(): StructuredDimension
    {
        return $this->first()->getStructuredDimension();
    }


    public function setIsBestOf(bool $isBestOf): void
    {
        foreach ($this as $drawing) {
            $drawing->setIsBestOf($isBestOf);
        }
    }


    public function getIsBestOf(): bool
    {
        return $this->first()->getIsBestOf();
    }


    public function setRestorationSurveys(array $restorationSurveys): void
    {
        foreach ($this as $drawing) {
            $drawing->setRestorationSurveys($restorationSurveys);
        }
    }


    public function getRestorationSurveys(): array
    {
        return $this->first()->getRestorationSurveys();
    }


    public function setSearchSortingNumber(string $searchSortingNumber): void
    {
        foreach ($this as $drawing) {
            $drawing->setSearchSortingNumber($searchSortingNumber);
        }
    }


    public function getSearchSortingNumber(): string
    {
        return $this->first()->getSearchSortingNumber();
    }


    public function setImages(array $images): void
    {
        foreach ($this as $drawing) {
            $drawing->setImages($images);
        }
    }


    public function getImages()
    {
        return $this->first()->getImages();
    }


    public function setDocuments(array $documents): void
    {
        foreach ($this as $drawing) {
            $drawing->setDocuments($documents);
        }
    }


    public function getDocuments()
    {
        return $this->first()->getDocuments();
    }
}
