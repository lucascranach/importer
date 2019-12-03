<?php

namespace CranachImport\Entities;

require_once 'ILanguageBaseItem.php';

require_once 'main/Person.php';
require_once 'main/PersonName.php';
require_once 'main/Title.php';

require_once 'graphic/Classification.php';
require_once 'graphic/Dating.php';
require_once 'graphic/GraphicReference.php';
require_once 'graphic/AdditionalTextInformation.php';
require_once 'graphic/Publication.php';
require_once 'graphic/MetaReference.php';
require_once 'graphic/CatalogWorkReference.php';
require_once 'graphic/StructuredDimension.php';

use CranachImport\Entities\ILanguageBaseItem;


/**
 * Representing a single graphic and all its data
 * 	One instance containing only data for one language
 */
class Graphic implements ILanguageBaseItem {

	public $langCode = '<unknown language>';

	public $involvedPersons = [];
	public $involvedPersonsNames = []; // Evtl. Umbau notwendig

	public $titles = [];
	public $classification = null;
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
	public $references = [];
	public $additionalTextInformation = [];
	public $publications = [];
	public $keywords = [];
	public $locations = [];
	public $repository = '';
	public $owner = '';
	public $sortingNumber = '';
	public $catalogWorkReferences = [];
	public $structuredDimension = null;


	function __construct() {

	}


	function setLangCode(string $langCode) {
		$this->langCode = $langCode;
	}


	function getLangCode(): string {
		return $this->langCode;
	}


	function addPerson(Main\Person $person) {
		$this->involvedPersons[] = $person;
	}


	function getPersons(): array {
		$this->involvedPersons;
	}


	function addPersonName(Main\PersonName $personName) {
		$this->involvedPersonsNames[] = $personName;
	}


	function getPersonNames(): array {
		$this->involvedPersonsNames;
	}


	function addTitle(Main\Title $title) {
		$this->titles[] = $title;
	}


	function getTitles(): array {
		return $this->titles;
	}


	function setClassification(Graphic\Classification $classification) {
		$this->classification = $classification;
	}


	function getClassification(): Graphic\Classification {
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


	function setObjectId(int $objectId) {
		$this->objectId = $objectId;
	}


	function getObjectId(): int {
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


	function setDating(Graphic\Dating $dating) {
		$this->dating = $dating;
	}


	function getDating(): ?Graphic\Dating {
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


	function addReference(Graphic\GraphicReference $reference) {
		$this->references[] = $reference;
	}


	function getReferences(): array {
		return $this->references;
	}


	function addAdditionalTextInformation(
		Graphic\AdditionalTextInformation $additonalTextInformation
	) {
		$this->additionalTextInformation[] = $additonalTextInformation;
	}


	function getAdditionalTextInformations() {
		return $this->additionalTextInformation;
	}


	function addPublication(Graphic\Publication $publication) {
		$this->publications[] = $publication;
	}


	function getPublications(): array {
		return $this->publications;
	}


	function addKeyword(Graphic\MetaReference $keyword) {
		$this->keywords[] = $keyword;
	}


	function getKeywords(): array {
		return $this->keywords;
	}


	function addLocation(Graphic\MetaReference $location) {
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


	function addCatalogWorkReference(
		Graphic\CatalogWorkReference $catalogWorkReference
	) {
		$this->catalogWorkReferences[] = $catalogWorkReference;
	}


	function getCatalogWorkReferences(): string {
		return $this->catalogWorkReferences;
	}


	function setStructuredDimension(
		Graphic\StructuredDimension $structuredDimension
	) {
		$this->structuredDimension = $structuredDimension;
	}


	function getStructuredDimension(): Graphic\StructuredDimension {
		return $this->structuredDimension;
	}

}