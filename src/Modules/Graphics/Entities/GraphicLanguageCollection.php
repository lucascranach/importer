<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Entities;

use CranachDigitalArchive\Importer\AbstractItemLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Main\Entities\AdditionalTextInformation;
use CranachDigitalArchive\Importer\Modules\Main\Entities\CatalogWorkReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Dating;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaLocationReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Person;
use CranachDigitalArchive\Importer\Modules\Main\Entities\PersonName;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Publication;
use CranachDigitalArchive\Importer\Modules\Main\Entities\StructuredDimension;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Title;
use CranachDigitalArchive\Importer\Modules\Graphics\Interfaces\IGraphic;

/**
 * @template-extends AbstractItemLanguageCollection<IGraphic>
 */
class GraphicLanguageCollection extends AbstractItemLanguageCollection implements IGraphic
{
    protected function createItem(): IGraphic
    {
        return new Graphic();
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
        foreach ($this as $graphic) {
            $graphic->setMetadata($metadata);
        }
    }


    public function getMetadata(): ?Metadata
    {
        return $this->first()->getMetadata();
    }


    public function addPerson(Person $person): void
    {
        foreach ($this as $graphic) {
            $graphic->addPerson($person);
        }
    }


    public function getPersons(): array
    {
        return $this->first()->getPersons();
    }


    public function addPersonName(PersonName $personName): void
    {
        foreach ($this as $graphic) {
            $graphic->addPersonName($personName);
        }
    }


    public function getPersonNames(): array
    {
        return $this->first()->getPersonNames();
    }


    public function addTitle(Title $title): void
    {
        foreach ($this as $graphic) {
            $graphic->addTitle($title);
        }
    }


    public function getTitles(): array
    {
        return $this->first()->getTitles();
    }


    public function setClassification(Classification $classification): void
    {
        foreach ($this as $graphic) {
            $graphic->setClassification($classification);
        }
    }


    public function getClassification(): ?Classification
    {
        return $this->first()->getClassification();
    }


    public function setConditionLevel(int $conditionLevel): void
    {
        foreach ($this as $graphic) {
            $graphic->setConditionLevel($conditionLevel);
        }
    }


    public function getConditionLevel(): int
    {
        return $this->first()->getConditionLevel();
    }

    public function setEditionNumber(int $editionNumber): void
    {
        foreach ($this as $graphic) {
            $graphic->setEditionNumber($editionNumber);
        }
    }

    public function getEditionNumber(): int
    {
        return $this->first()->getEditionNumber();
    }

    public function setObjectName(string $objectName): void
    {
        foreach ($this as $graphic) {
            $graphic->setObjectName($objectName);
        }
    }


    public function getObjectName(): string
    {
        return $this->first()->getObjectName();
    }


    public function getInventoryNumberPrefix(): string
    {
        return $this->first()->getInventoryNumberPrefix();
    }


    public function setInventoryNumberPrefix(string $inventoryNumberPrefix)
    {
        foreach ($this as $graphic) {
            $graphic->setInventoryNumberPrefix($inventoryNumberPrefix);
        }
    }


    public function setInventoryNumber(string $inventoryNumber): void
    {
        foreach ($this as $graphic) {
            $graphic->setInventoryNumber($inventoryNumber);
        }
    }


    public function getInventoryNumber(): string
    {
        return $this->first()->getInventoryNumber();
    }


    public function setObjectId(int $objectId): void
    {
        foreach ($this as $graphic) {
            $graphic->setObjectId($objectId);
        }
    }


    public function getObjectId(): ?int
    {
        return $this->first()->getObjectId();
    }


    public function setIsVirtual(bool $isVirtual): void
    {
        foreach ($this as $graphic) {
            $graphic->setIsVirtual($isVirtual);
        }
    }


    public function getIsVirtual(): bool
    {
        return $this->first()->getIsVirtual();
    }


    public function setDimensions(string $dimensions): void
    {
        foreach ($this as $graphic) {
            $graphic->setDimensions($dimensions);
        }
    }


    public function getDimensions(): string
    {
        return $this->first()->getDimensions();
    }


    public function setDating(Dating $dating): void
    {
        foreach ($this as $graphic) {
            $graphic->setDating($dating);
        }
    }


    public function getDating(): ?Dating
    {
        return $this->first()->getDating();
    }


    public function setDescription(string $description): void
    {
        foreach ($this as $graphic) {
            $graphic->setDescription($description);
        }
    }


    public function getDescription(): string
    {
        return $this->first()->getDescription();
    }


    public function setProvenance(string $provenance): void
    {
        foreach ($this as $graphic) {
            $graphic->setProvenance($provenance);
        }
    }


    public function getProvenance(): string
    {
        return $this->first()->getProvenance();
    }


    public function setMedium(string $medium): void
    {
        foreach ($this as $graphic) {
            $graphic->setMedium($medium);
        }
    }


    public function getMedium(): string
    {
        return $this->first()->getMedium();
    }


    public function setSignature(string $signature): void
    {
        foreach ($this as $graphic) {
            $graphic->setSignature($signature);
        }
    }


    public function getSignature(): string
    {
        return $this->first()->getSignature();
    }


    public function setInscription(string $inscription): void
    {
        foreach ($this as $graphic) {
            $graphic->setInscription($inscription);
        }
    }


    public function getInscription(): string
    {
        return $this->first()->getInscription();
    }


    public function setMarkings(string $markings): void
    {
        foreach ($this as $graphic) {
            $graphic->setMarkings($markings);
        }
    }


    public function getMarkings(): string
    {
        return $this->first()->getMarkings();
    }


    public function setRelatedWorks(string $relatedWorks): void
    {
        foreach ($this as $graphic) {
            $graphic->setRelatedWorks($relatedWorks);
        }
    }


    public function getRelatedWorks(): string
    {
        return $this->first()->getRelatedWorks();
    }


    public function setExhibitionHistory(string $exhibitionHistory): void
    {
        foreach ($this as $graphic) {
            $graphic->setExhibitionHistory($exhibitionHistory);
        }
    }


    public function getExhibitionHistory(): string
    {
        return $this->first()->getExhibitionHistory();
    }


    public function setBibliography(string $bibliography): void
    {
        foreach ($this as $graphic) {
            $graphic->setBibliography($bibliography);
        }
    }


    public function getBibliography(): string
    {
        return $this->first()->getBibliography();
    }


    public function addReprintReference(ObjectReference $reference): void
    {
        foreach ($this as $graphic) {
            $graphic->addReprintReference($reference);
        }
    }


    public function setReprintReferences(array $references): void
    {
        foreach ($this as $graphic) {
            $graphic->setReprintReferences($references);
        }
    }


    public function getReprintReferences(): array
    {
        return $this->first()->getReprintReferences();
    }


    public function addPartOfWorkReference(ObjectReference $reference): void
    {
        foreach ($this as $graphic) {
            $graphic->addPartOfWorkReference($reference);
        }
    }


    public function setPartOfWorkReferences(array $references): void
    {
        foreach ($this as $graphic) {
            $graphic->setPartOfWorkReferences($references);
        }
    }


    public function getPartOfWorkReferences(): array
    {
        return $this->first()->getPartOfWorkReferences();
    }


    public function addPartOfSerieReference(ObjectReference $reference): void
    {
        foreach ($this as $graphic) {
            $graphic->addPartOfSerieReference($reference);
        }
    }


    public function setPartOfSerieReferences(array $references): void
    {
        foreach ($this as $graphic) {
            $graphic->setPartOfSerieReferences($references);
        }
    }


    public function getPartOfSerieReferences(): array
    {
        return $this->first()->getPartOfSerieReferences();
    }


    public function addOnSameSheetReference(ObjectReference $reference): void
    {
        foreach ($this as $graphic) {
            $graphic->addOnSameSheetReference($reference);
        }
    }


    public function setOnSameSheetReferences(array $references): void
    {
        foreach ($this as $graphic) {
            $graphic->setOnSameSheetReferences($references);
        }
    }


    public function getOnSameSheetReferences(): array
    {
        return $this->first()->getOnSameSheetReferences();
    }


    public function addIdenticalWatermarkReference(ObjectReference $reference): void
    {
        foreach ($this as $graphic) {
            $graphic->addIdenticalWatermarkReference($reference);
        }
    }


    public function setIdenticalWatermarkReferences(array $references): void
    {
        foreach ($this as $graphic) {
            $graphic->setIdenticalWatermarkReferences($references);
        }
    }


    public function getIdenticalWatermarkReferences(): array
    {
        return $this->first()->getIdenticalWatermarkReferences();
    }


    public function addAdditionalTextInformation(AdditionalTextInformation $additionalTextInformation): void
    {
        foreach ($this as $graphic) {
            $graphic->addAdditionalTextInformation($additionalTextInformation);
        }
    }


    public function getAdditionalTextInformations(): array
    {
        return $this->first()->getAdditionalTextInformations();
    }


    public function addPublication(Publication $publication): void
    {
        foreach ($this as $graphic) {
            $graphic->addPublication($publication);
        }
    }


    public function getPublications(): array
    {
        return $this->first()->getPublications();
    }


    public function addKeyword(MetaReference $keyword): void
    {
        foreach ($this as $graphic) {
            $graphic->addKeyword($keyword);
        }
    }


    public function getKeywords(): array
    {
        return $this->first()->getKeywords();
    }


    public function setLocations(array $locations): void
    {
        foreach ($this as $graphic) {
            $graphic->setLocations($locations);
        }
    }


    public function addLocation(MetaLocationReference $location): void
    {
        foreach ($this as $graphic) {
            $graphic->addLocation($location);
        }
    }


    public function getLocations(): array
    {
        return $this->first()->getLocations();
    }


    public function setRepository(string $repository): void
    {
        foreach ($this as $graphic) {
            $graphic->setRepository($repository);
        }
    }


    public function getRepository(): string
    {
        return $this->first()->getRepository();
    }


    public function setOwner(string $owner): void
    {
        foreach ($this as $graphic) {
            $graphic->setOwner($owner);
        }
    }


    public function getOwner(): string
    {
        return $this->first()->getOwner();
    }


    public function setCollectionRepositoryId(string $id): void
    {
        foreach ($this as $graphic) {
            $graphic->setCollectionRepositoryId($id);
        }
    }


    public function setSortingNumber(string $sortingNumber): void
    {
        foreach ($this as $graphic) {
            $graphic->setSortingNumber($sortingNumber);
        }
    }


    public function getSortingNumber(): string
    {
        return $this->first()->getSortingNumber();
    }


    public function addCatalogWorkReference(CatalogWorkReference $catalogWorkReference): void
    {
        foreach ($this as $graphic) {
            $graphic->addCatalogWorkReference($catalogWorkReference);
        }
    }


    public function getCatalogWorkReferences(): array
    {
        return $this->first()->getCatalogWorkReferences();
    }


    public function setStructuredDimension(StructuredDimension $structuredDimension): void
    {
        foreach ($this as $graphic) {
            $graphic->setStructuredDimension($structuredDimension);
        }
    }


    public function getStructuredDimension(): ?StructuredDimension
    {
        return $this->first()->getStructuredDimension();
    }


    public function setRestorationSurveys(array $restorationSurveys): void
    {
        foreach ($this as $graphic) {
            $graphic->setRestorationSurveys($restorationSurveys);
        }
    }


    public function getRestorationSurveys(): array
    {
        return $this->first()->getRestorationSurveys();
    }


    public function setSearchSortingNumber(string $searchSortingNumber): void
    {
        foreach ($this as $graphic) {
            $graphic->setSearchSortingNumber($searchSortingNumber);
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
