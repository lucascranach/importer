<?php

namespace CranachImport\Entities;

require_once 'IBaseItem.php';

require_once 'literatureReference/Event.php';
require_once 'literatureReference/Person.php';
require_once 'literatureReference/Publication.php';
require_once 'literatureReference/ConnectedObject.php';

use CranachImport\Entities\LiteratureReference\Event;
use CranachImport\Entities\LiteratureReference\Person;
use CranachImport\Entities\LiteratureReference\Publication;
use CranachImport\Entities\LiteratureReference\ConnectedObject;


/**
 * Representing a single literature reference
 */
class LiteratureReference implements IBaseItem {

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


	function __construct() {

	}


	function setReferenceId(string $referenceId) {
		$this->referenceId = $referenceId;
	}


	function getReferenceId(): string {
		return $this->referenceId;
	}


	function setReferenceNumber(string $referenceNumber) {
		$this->referenceNumber = $referenceNumber;
	}


	function getReferenceNumber(): string {
		return $this->referenceNumber;
	}


	function setTitle(string $title) {
		$this->title = $title;
	}


	function getTitle(): string {
		return $this->title;
	}


	function setSubtitle(string $subtitle) {
		$this->subtitle = $subtitle;
	}


	function getSubtitle(): string {
		return $this->subtitle;
	}


	function setShorttitle(string $shorttitle) {
		$this->shorttitle = $shorttitle;
	}


	function getShorttitle(): string {
		return $this->shorttitle;
	}


	function setJournal(string $journal) {
		$this->journal = $journal;
	}


	function getJournal(): string {
		return $this->journal;
	}


	function setSeries(string $series) {
		$this->series = $series;
	}


	function getSeries(): string {
		return $this->series;
	}


	function setVolume(string $volume) {
		$this->volume = $volume;
	}


	function getVolume(): string {
		return $this->volume;
	}


	function setEdition(string $edition) {
		$this->edition = $edition;
	}


	function getEdition(): string {
		return $this->edition;
	}


	function setPublishLocation(string $publishLocation) {
		$this->publishLocation = $publishLocation;
	}


	function getPublishLocation(): string {
		return $this->publishLocation;
	}


	function setPublishDate(string $publishDate) {
		$this->publishDate = $publishDate;
	}


	function getPublishDate(): string {
		return $this->publishDate;
	}


	function setPageNumbers(string $pageNumbers) {
		$this->pageNumbers = $pageNumbers;
	}


	function getPageNumbers(): string {
		return $this->pageNumbers;
	}


	function setDate(string $date) {
		$this->date = $date;
	}


	function getDate(): string {
		return $this->date;
	}

	function addEvent(Event $event) {
		$this->events[] = $event;
	}


	function getEvents(): array {
		return $this->events;
	}


	function setCopyright(string $copyright) {
		$this->copyright = $copyright;
	}


	function getCopyright(): string {
		return $this->copyright;
	}


	function addPerson(Person $person) {
		$this->persons[] = $person;
	}


	function getPersons(): array {
		return $this->persons;
	}


	function addPublication(Publication $publication) {
		$this->publications[] = $publication;
	}


	function getPublications(): array {
		return $this->publications;
	}


	function setId(string $id) {
		$this->id = $id;
	}


	function getId(): array {
		return $this->id;
	}


	function addConnectedObject(ConnectedObject $connectedObject) {
		$this->connectedObjects[] = $connectedObject;
	}


	function getConnectedObjects(): array {
		return $this->connectedObjects;
	}

}