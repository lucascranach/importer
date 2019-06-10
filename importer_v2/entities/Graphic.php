<?php

namespace CranachImport\Entities;

require_once 'entities/IBaseItem.php';

require_once 'Person.php';
require_once 'PersonName.php';
require_once 'Title.php';
require_once 'Dating.php';
require_once 'Reprint.php';
require_once 'AdditionalTextInformationEntry.php';
require_once 'Publication.php';
require_once 'MetaReference.php';
require_once 'CatalogWorkReference.php';
require_once 'StructuredDimensionsEntry.php';

use CranachImport\Entities\IBaseItem;


class Graphic implements IBaseItem {

	public $langCode = '<unknown language>';

	public $involvedPersons = [];
	public $involvedPersonsNames = []; // Evtl. Umbau notwendig

	public $titles = [];
	public $classification = '';
	public $objectName = '';
	public $inventoryNumber = '';
	public $objectId = '';
	public $isVirtual = false;
	public $dimensions = '';
	public $date = null;
	public $description = '';
	public $provenance = '';
	public $medium = '';
	public $signature = '';
	public $inscription = '';
	public $markings = '';
	public $relatedWorks = '';
	public $exhibitionHistory = '';
	public $bibliography = '';
	public $reprints = [];
	public $secondaryReprints = [];
	public $additionalTextInformationEntries = [];
	public $publications = [];
	public $keywords = [];
	public $locations = [];
	public $repository = '';
	public $owner = '';
	public $sortingNumber = '';
	public $catalogReferences = [];
	public $structuredDimensionsEntries = [];


	function __construct() {

	}


	function setLangCode(string $langCode) {
		$this->langCode = $langCode;
	}


	function getLangCode(): string {
		return $this->langCode;
	}


	function addPerson(Person $person) {
		$this->involvedPersons[] = $person;
	}


	function getPersons(): array {
		$this->involvedPersons;
	}


	function addPersonName(PersonName $personName) {
		$this->involvedPersonsNames[] = $personName;
	}


	function getPersonNames(): array {
		$this->involvedPersonsNames;
	}


	function addTitle(Title $title) {
		$this->title = $titles;
	}


	function getTitles(): array {
		return $this->titles;
	}


	function setClassification(string $classification) {
		$this->classification = $classification;
	}


	function getClassification(): string {
		return $this->classification;
	}


	function setObjectName(string $objectName) {
		$this->objectName = $objectName;
	}


	function getObjectName(): string {
		return $this->objectName;
	}


	function setInventoryNumber(string $inventoryNumber) {
		$this->inventoryNumber = $inventoryNumber;
	}


	function getInventoryNumber(): string {
		return $this->inventoryNumber;
	}


	function setObjectId(string $objectId) {
		$this->objectId = $objectId;
	}


	function getObjectId(): string {
		return $this->objectId;
	}


	function setIsVirtual(bool $isVirtual) {
		$this->isVirtual = $isVirtual;
	}


	function getIsVirtual(): bool {
		return $this->isVirtual;
	}


	function setDimensions(string $dimensions) {
		$this->dimensions = $dimensions;
	}


	function getDimensions(): string {
		return $this->dimensions;
	}


	function setDating(Dating $dating) {
		$this->dating = $dating;
	}


	function getDating(): ?Dating {
		return $this->dating;
	}


	function setDescription(string $description) {
		$this->description = $description;
	}


	function getDescription(): string {
		return $this->description;
	}


	function setProvenance(string $provenance) {
		$this->provenance = $provenance;
	}


	function getProvenance(): string {
		return $this->provenance;
	}


	function setMedium(string $medium) {
		$this->medium = $medium;
	}


	function getMedium(): string {
		return $this->medium;
	}


	function setSignature(string $signature) {
		$this->signature = $signature;
	}


	function getSignature(): string {
		return $this->signature;
	}


	function setInscription(string $inscription) {
		$this->inscription = $inscription;
	}


	function getInscription(): string {
		return $this->inscription;
	}


	function setMarkings(string $markings) {
		$this->markings = $markings;
	}


	function getMarkings(): string {
		return $this->markings;
	}


	function setRelatedWorks(string $relatedWorks) {
		$this->relatedWorks = $relatedWorks;
	}


	function getRelatedWorks(): string {
		return $this->relatedWorks;
	}


	function setExhibitionHistory(string $exhibitionHistory) {
		$this->exhibitionHistory = $exhibitionHistory;
	}


	function getExhibitionHistory(): string {
		return $this->exhibitionHistory;
	}


	function setBibliography(string $bibliography) {
		$this->bibliography = $bibliography;
	}


	function getBibliography(): string {
		return $this->bibliography;
	}


	function addReprint(Reprint $reprint) {
		$this->reprints[] = $reprint;
	}


	function getReprints(): array {
		return $this->reprints;
	}


	function addSecondaryReprint(Reprint $secondaryReprint) {
		$this->secondaryReprints[] = $secondaryReprint;
	}


	function getSecondaryReprints(): array {
		return $this->secondaryReprints;
	}


	function addPublication(Publication $publication) {
		$this->publications[] = $publication;
	}


	function getPublications(): array {
		return $this->publications;
	}


	function addKeyword(MetaReference $keyword) {
		$this->keywords[] = $keyword;
	}


	function getKeywords(): array {
		return $this->keywords;
	}


	function addLocation(MetaReference $location) {
		$this->locations[] = $location;
	}


	function getLocations(): array {
		return $this->locations;
	}


	function setRepository(string $repository) {
		$this->repository = $repository;
	}


	function getRepository(): string {
		return $this->repository;
	}


	function setOwner(string $owner) {
		$this->owner = $owner;
	}


	function getOwner(): string {
		return $this->owner;
	}


	function setSortingNumber(string $sortingNumber) {
		$this->sortingNumber = $sortingNumber;
	}


	function getSortingNumber(): string {
		return $this->sortingNumber;
	}


	function addCatalogWorkReference(CatalogWorkReference $catalogWorkReference) {
		$this->catalogWorkReferences[] = $catalogWorkReference;
	}


	function getCatalogWorkReferences(): string {
		return $this->catalogWorkReferences;
	}


	function addStructuredDimensionEntry(StructuredDimensionsEntry $structuredDimensionEntry) {
		$this->structuredDimensionsEntries[] = $structuredDimensionEntry;
	}


	function getStructuredDimensionsEntries(): array {
		return $this->structuredDimensionsEntries;
	}

}