<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Entities;

use CranachDigitalArchive\Importer\Interfaces\Entities\ILanguageBaseItem;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Person;
use CranachDigitalArchive\Importer\Modules\Main\Entities\PersonName;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Title;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Dating;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\AdditionalTextInformation;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Publication;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\CatalogWorkReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\StructuredDimension;

/**
 * Representing a single graphic and all its data
 * 	One instance containing only data for one language
 */
class Graphic implements ILanguageBaseItem
{
    public $langCode = '<unknown language>';

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

    public $images = null;

    /* Awaited images structure if images exist
        [
            'infos' => [
                'maxDimensions' => [ 'width' => 0, 'height' => 0 ],
            ],
            'sizes' => [
                'xs' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
                's' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
                'm' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
                'l' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
                'xl' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
            ],
        ]
    */


    public function __construct()
    {
    }


    public function setLangCode(string $langCode)
    {
        $this->langCode = $langCode;
    }


    public function getLangCode(): string
    {
        return $this->langCode;
    }


    public function addPerson(Person $person)
    {
        $this->involvedPersons[] = $person;
    }


    public function getPersons(): array
    {
        return $this->involvedPersons;
    }


    public function addPersonName(PersonName $personName)
    {
        $this->involvedPersonsNames[] = $personName;
    }


    public function getPersonNames(): array
    {
        return $this->involvedPersonsNames;
    }


    public function addTitle(Title $title)
    {
        $this->titles[] = $title;
    }


    public function getTitles(): array
    {
        return $this->titles;
    }


    public function setClassification(Classification $classification)
    {
        $this->classification = $classification;
    }


    public function getClassification(): Classification
    {
        return $this->classification;
    }


    public function setConditionLevel(int $conditionLevel)
    {
        $this->conditionLevel = $conditionLevel;
    }


    public function getConditionLevel(): int
    {
        return $this->conditionLevel;
    }


    public function setObjectName(string $objectName)
    {
        $this->objectName = $objectName;
    }


    public function getObjectName(): string
    {
        return $this->objectName;
    }


    public function setInventoryNumber(string $inventoryNumber)
    {
        $this->inventoryNumber = $inventoryNumber;
    }


    public function getInventoryNumber(): string
    {
        return $this->inventoryNumber;
    }


    public function setObjectId(int $objectId)
    {
        $this->objectId = $objectId;
    }


    public function getObjectId(): int
    {
        return $this->objectId;
    }


    public function setIsVirtual(bool $isVirtual)
    {
        $this->isVirtual = $isVirtual;
    }


    public function getIsVirtual(): bool
    {
        return $this->isVirtual;
    }


    public function setDimensions(string $dimensions)
    {
        $this->dimensions = $dimensions;
    }


    public function getDimensions(): string
    {
        return $this->dimensions;
    }


    public function setDating(Dating $dating)
    {
        $this->dating = $dating;
    }


    public function getDating(): ?Dating
    {
        return $this->dating;
    }


    public function setDescription(string $description)
    {
        $this->description = $description;
    }


    public function getDescription(): string
    {
        return $this->description;
    }


    public function setProvenance(string $provenance)
    {
        $this->provenance = $provenance;
    }


    public function getProvenance(): string
    {
        return $this->provenance;
    }


    public function setMedium(string $medium)
    {
        $this->medium = $medium;
    }


    public function getMedium(): string
    {
        return $this->medium;
    }


    public function setSignature(string $signature)
    {
        $this->signature = $signature;
    }


    public function getSignature(): string
    {
        return $this->signature;
    }


    public function setInscription(string $inscription)
    {
        $this->inscription = $inscription;
    }


    public function getInscription(): string
    {
        return $this->inscription;
    }


    public function setMarkings(string $markings)
    {
        $this->markings = $markings;
    }


    public function getMarkings(): string
    {
        return $this->markings;
    }


    public function setRelatedWorks(string $relatedWorks)
    {
        $this->relatedWorks = $relatedWorks;
    }


    public function getRelatedWorks(): string
    {
        return $this->relatedWorks;
    }


    public function setExhibitionHistory(string $exhibitionHistory)
    {
        $this->exhibitionHistory = $exhibitionHistory;
    }


    public function getExhibitionHistory(): string
    {
        return $this->exhibitionHistory;
    }


    public function setBibliography(string $bibliography)
    {
        $this->bibliography = $bibliography;
    }


    public function getBibliography(): string
    {
        return $this->bibliography;
    }


    public function addReprintReference(ObjectReference $reference)
    {
        $this->references['reprints'][] = $reference;
    }


    public function setReprintReferences(array $references)
    {
        $this->references['reprints'] = $references;
    }


    public function getReprintReferences(): array
    {
        return $this->references['reprints'];
    }


    public function addRelatedWorkReference(ObjectReference $reference)
    {
        $this->references['relatedWorks'][] = $reference;
    }


    public function setRelatedWorkReferences(array $references)
    {
        $this->references['relatedWorks'] = $references;
    }


    public function getRelatedWorkReferences(): array
    {
        return $this->references['relatedWorks'];
    }


    public function addAdditionalTextInformation(AdditionalTextInformation $additionalTextInformation)
    {
        $this->additionalTextInformation[] = $additionalTextInformation;
    }


    public function getAdditionalTextInformations(): array
    {
        return $this->additionalTextInformation;
    }


    public function addPublication(Publication $publication)
    {
        $this->publications[] = $publication;
    }


    public function getPublications(): array
    {
        return $this->publications;
    }


    public function addKeyword(MetaReference $keyword)
    {
        $this->keywords[] = $keyword;
    }


    public function getKeywords(): array
    {
        return $this->keywords;
    }


    public function addLocation(MetaReference $location)
    {
        $this->locations[] = $location;
    }


    public function getLocations(): array
    {
        return $this->locations;
    }


    public function setRepository(string $repository)
    {
        $this->repository = $repository;
    }


    public function getRepository(): string
    {
        return $this->repository;
    }


    public function setOwner(string $owner)
    {
        $this->owner = $owner;
    }


    public function getOwner(): string
    {
        return $this->owner;
    }


    public function setSortingNumber(string $sortingNumber)
    {
        $this->sortingNumber = $sortingNumber;
    }


    public function getSortingNumber(): string
    {
        return $this->sortingNumber;
    }


    public function addCatalogWorkReference(CatalogWorkReference $catalogWorkReference)
    {
        $this->catalogWorkReferences[] = $catalogWorkReference;
    }


    public function getCatalogWorkReferences(): array
    {
        return $this->catalogWorkReferences;
    }


    public function setStructuredDimension(StructuredDimension $structuredDimension)
    {
        $this->structuredDimension = $structuredDimension;
    }


    public function getStructuredDimension(): StructuredDimension
    {
        return $this->structuredDimension;
    }


    public function setImages(?array $images)
    {
        $this->images = $images;
    }


    public function getImages(): array
    {
        return $this->images;
    }
}
