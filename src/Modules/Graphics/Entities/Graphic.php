<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Entities;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;
use CranachDigitalArchive\Importer\Modules\Main\Entities\AbstractImagesItem;
use CranachDigitalArchive\Importer\Modules\Main\Entities\AdditionalTextInformation;
use CranachDigitalArchive\Importer\Modules\Main\Entities\CatalogWorkReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Dating;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Person;
use CranachDigitalArchive\Importer\Modules\Main\Entities\PersonName;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Publication;
use CranachDigitalArchive\Importer\Modules\Main\Entities\StructuredDimension;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Title;

/**
 * Representing a single graphic and all its data
 *     One instance containing only data for one language
 */
class Graphic extends AbstractImagesItem implements IBaseItem
{
    const ENTITY_TYPE = 'GRAPHIC';

    public $metadata = null;
    public $involvedPersons = [];
    public $involvedPersonsNames = []; // Evtl. Umbau notwendig

    public $titles = [];
    public $classification = null;
    public $conditionLevel = 0;
    public $objectName = '';
    public $inventoryNumber = '';
    public $objectId = null;
    public $isVirtual = false;
    public $dimensions = '';
    public $dating = null;
    public $description = '';
    public $provenance = '';
    public $medium = '';
    public $signature = '';
    public $inscription = '';
    public $markings = '';
    public $relatedWorks = '';
    public $exhibitionHistory = '';
    public $representativeObject = '';
    public $bibliography = '';
    public $references = [
        'reprints' => [],
        'relatedWorks' => [],
    ];
    public $additionalTextInformation = [];
    public $publications = [];
    public $keywords = [];
    public $locations = [];
    public $repository = '';
    public $owner = '';
    public $sortingNumber = '';
    public $catalogWorkReferences = [];
    public $structuredDimension = null;
    public $restorationSurveys = [];
    private $clearingPatterns = [
        ['find' => 'GWN_', 'replaceWith' => ''],
        ['find' => 'G_G_', 'replaceWith' => ''],
    ];

    public function __construct()
    {
    }

    public function getId(): string
    {
        return $this->getInventoryNumber();
    }

    public function getImageId(): string
    {
        $id = $this->getId();

        /* We want to use the representative object inventory number if one exists */
        if (!empty($this->getRepresentativeObject())) {
            $id = $this->getRepresentativeObject();
        }



        return empty($id) ? $id : 'G_' . $id;
    }

    public function setMetadata(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getMetadata(): ?Metadata
    {
        return $this->metadata;
    }

    public function addPerson(Person $person): void
    {
        $this->involvedPersons[] = $person;
    }

    public function getPersons(): array
    {
        return $this->involvedPersons;
    }

    public function addPersonName(PersonName $personName): void
    {
        $this->involvedPersonsNames[] = $personName;
    }

    public function getPersonNames(): array
    {
        return $this->involvedPersonsNames;
    }

    public function addTitle(Title $title): void
    {
        $this->titles[] = $title;
    }

    public function getTitles(): array
    {
        return $this->titles;
    }

    public function setClassification(Classification $classification): void
    {
        $this->classification = $classification;
    }

    public function getClassification(): ?Classification
    {
        return $this->classification;
    }

    public function setConditionLevel(int $conditionLevel): void
    {
        $this->conditionLevel = $conditionLevel;
    }

    public function getConditionLevel(): int
    {
        return $this->conditionLevel;
    }

    public function setObjectName(string $objectName): void
    {
        $this->objectName = $objectName;
    }

    public function getObjectName(): string
    {
        return $this->objectName;
    }

    public function setInventoryNumber(string $inventoryNumber): void
    {
        foreach ($this->clearingPatterns as $clearingPattern) {
            $find = '=' . $clearingPattern['find'] . '=';
            $replaceWith = $clearingPattern['replaceWith'];
            $inventoryNumber = preg_replace($find, $replaceWith, $inventoryNumber);
        }
        $this->inventoryNumber = $inventoryNumber;
    }

    public function getInventoryNumber(): string
    {
        return $this->inventoryNumber;
    }

    public function setObjectId(int $objectId): void
    {
        $this->objectId = $objectId;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    public function setIsVirtual(bool $isVirtual): void
    {
        $this->isVirtual = $isVirtual;
    }

    public function getIsVirtual(): bool
    {
        return $this->isVirtual;
    }

    public function setDimensions(string $dimensions): void
    {
        $this->dimensions = $dimensions;
    }

    public function getDimensions(): string
    {
        return $this->dimensions;
    }

    public function setDating(Dating $dating): void
    {
        $this->dating = $dating;
    }

    public function getDating(): ?Dating
    {
        return $this->dating;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setProvenance(string $provenance): void
    {
        $this->provenance = $provenance;
    }

    public function getProvenance(): string
    {
        return $this->provenance;
    }

    public function setMedium(string $medium): void
    {
        $this->medium = $medium;
    }

    public function getMedium(): string
    {
        return $this->medium;
    }

    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function setInscription(string $inscription): void
    {
        $this->inscription = $inscription;
    }

    public function getInscription(): string
    {
        return $this->inscription;
    }

    public function setMarkings(string $markings): void
    {
        $this->markings = $markings;
    }

    public function getMarkings(): string
    {
        return $this->markings;
    }

    public function setRelatedWorks(string $relatedWorks): void
    {
        $this->relatedWorks = $relatedWorks;
    }

    public function getRelatedWorks(): string
    {
        return $this->relatedWorks;
    }

    public function setExhibitionHistory(string $exhibitionHistory): void
    {
        $this->exhibitionHistory = $exhibitionHistory;
    }

    public function getExhibitionHistory(): string
    {
        return $this->exhibitionHistory;
    }

    public function setRepresentativeObject(string $representativeObject): void
    {
        $this->representativeObject = $representativeObject;
    }

    public function getRepresentativeObject(): string
    {
        return $this->representativeObject;
    }

    public function setBibliography(string $bibliography): void
    {
        $this->bibliography = $bibliography;
    }

    public function getBibliography(): string
    {
        return $this->bibliography;
    }

    public function addReprintReference(ObjectReference $reference): void
    {
        $this->references['reprints'][] = $reference;
    }

    public function setReprintReferences(array $references): void
    {
        $this->references['reprints'] = $references;
    }

    public function getReprintReferences(): array
    {
        return $this->references['reprints'];
    }

    public function addRelatedWorkReference(ObjectReference $reference): void
    {
        $this->references['relatedWorks'][] = $reference;
    }

    public function setRelatedWorkReferences(array $references): void
    {
        $this->references['relatedWorks'] = $references;
    }

    public function getRelatedWorkReferences(): array
    {
        return $this->references['relatedWorks'];
    }

    public function addAdditionalTextInformation(AdditionalTextInformation $additionalTextInformation): void
    {
        $this->additionalTextInformation[] = $additionalTextInformation;
    }

    public function getAdditionalTextInformations(): array
    {
        return $this->additionalTextInformation;
    }

    public function addPublication(Publication $publication): void
    {
        $this->publications[] = $publication;
    }

    public function getPublications(): array
    {
        return $this->publications;
    }

    public function addKeyword(MetaReference $keyword): void
    {
        $this->keywords[] = $keyword;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function addLocation(MetaReference $location): void
    {
        $this->locations[] = $location;
    }

    public function getLocations(): array
    {
        return $this->locations;
    }

    public function setRepository(string $repository): void
    {
        $this->repository = $repository;
    }

    public function getRepository(): string
    {
        return $this->repository;
    }

    public function setOwner(string $owner): void
    {
        $this->owner = $owner;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function setSortingNumber(string $sortingNumber): void
    {
        $this->sortingNumber = $sortingNumber;
    }

    public function getSortingNumber(): string
    {
        return $this->sortingNumber;
    }

    public function addCatalogWorkReference(CatalogWorkReference $catalogWorkReference): void
    {
        $this->catalogWorkReferences[] = $catalogWorkReference;
    }

    public function getCatalogWorkReferences(): array
    {
        return $this->catalogWorkReferences;
    }

    public function setStructuredDimension(StructuredDimension $structuredDimension): void
    {
        $this->structuredDimension = $structuredDimension;
    }

    public function getStructuredDimension(): ?StructuredDimension
    {
        return $this->structuredDimension;
    }

    public function setRestorationSurveys(array $restorationSurveys): void
    {
        $this->restorationSurveys = $restorationSurveys;
    }

    public function getRestorationSurveys(): array
    {
        return $this->restorationSurveys;
    }
}
