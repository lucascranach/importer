<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Interfaces;

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
use CranachDigitalArchive\Importer\Interfaces\Entities\IImagesItem;
use CranachDigitalArchive\Importer\Interfaces\Entities\ILocations;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Classification;

interface IGraphic extends IImagesItem, ILocations
{
    public function getId(): string;

    public function setMetadata(Metadata $metadata);

    public function addPerson(Person $person): void;

    public function getPersons(): array;

    public function addPersonName(PersonName $personName): void;

    public function getPersonNames(): array;

    public function addTitle(Title $title): void;

    public function getTitles(): array;

    public function setClassification(Classification $classification): void;

    public function getClassification(): ?Classification;

    public function setConditionLevel(int $conditionLevel): void;

    public function getConditionLevel(): int;

    public function setObjectName(string $objectName): void;

    public function getObjectName(): string;

    public function getInventoryNumberPrefix(): string;

    public function setInventoryNumberPrefix(string $inventoryNumberPrefix);

    public function setInventoryNumber(string $inventoryNumber): void;

    public function getInventoryNumber(): string;

    public function setObjectId(int $objectId): void;

    public function getObjectId(): ?int;

    public function setIsVirtual(bool $isVirtual): void;

    public function getIsVirtual(): bool;

    public function setDimensions(string $dimensions): void;

    public function getDimensions(): string;

    public function setDating(Dating $dating): void;

    public function getDating(): ?Dating;

    public function setDescription(string $description): void;

    public function getDescription(): string;

    public function setProvenance(string $provenance): void;

    public function getProvenance(): string;

    public function setMedium(string $medium): void;

    public function getMedium(): string;

    public function setSignature(string $signature): void;

    public function getSignature(): string;

    public function setInscription(string $inscription): void;

    public function getInscription(): string;

    public function setMarkings(string $markings): void;

    public function getMarkings(): string;

    public function setRelatedWorks(string $relatedWorks): void;

    public function getRelatedWorks(): string;

    public function setExhibitionHistory(string $exhibitionHistory): void;

    public function getExhibitionHistory(): string;

    public function setBibliography(string $bibliography): void;

    public function getBibliography(): string;

    public function addReprintReference(ObjectReference $reference): void;

    public function setReprintReferences(array $references): void;

    public function getReprintReferences(): array;

    public function addRelatedWorkReference(ObjectReference $reference): void;

    public function setRelatedWorkReferences(array $references): void;

    public function getRelatedWorkReferences(): array;

    public function addAdditionalTextInformation(AdditionalTextInformation $additionalTextInformation): void;

    public function getAdditionalTextInformations(): array;

    public function addPublication(Publication $publication): void;

    public function getPublications(): array;

    public function addKeyword(MetaReference $keyword): void;

    public function getKeywords(): array;

    public function addLocation(MetaLocationReference $location): void;

    public function setRepository(string $repository): void;

    public function getRepository(): string;

    public function setOwner(string $owner): void;

    public function getOwner(): string;

    public function setCollectionRepositoryId(string $id): void;

    public function setSortingNumber(string $sortingNumber): void;

    public function getSortingNumber(): string;

    public function addCatalogWorkReference(CatalogWorkReference $catalogWorkReference): void;

    public function getCatalogWorkReferences(): array;

    public function setStructuredDimension(StructuredDimension $structuredDimension): void;

    public function getStructuredDimension(): ?StructuredDimension;

    public function setRestorationSurveys(array $restorationSurveys): void;

    public function getRestorationSurveys(): array;

    public function setSearchSortingNumber(string $searchSortingNumber): void;

    public function getSearchSortingNumber(): string;
}
