<?php

namespace CranachImport\Entities;


/**
 * Representing a single historic event information
 *  Used for dating purposes
 */
class HistoricEventInformation {

	public $eventType = '';
	public $text = '';
	public $begin = null;
	public $end = null;
	public $remarks = '';


	function __construct() {

	}


	function setEventType(string $eventType) {
		$this->eventType = $eventType;
	}


	function getEventType(): string {
		return $this->eventType;
	}


	function setText(string $text) {
		$this->text = $text;
	}


	function getText(): string {
		return $this->text;
	}


	function setBegin(int $begin) {
		$this->begin = $begin;
	}


	function getBegin(): ?int {
		return $this->begin;
	}


	function setEnd(int $end) {
		$this->end = $end;
	}


	function getEnd(): ?int {
		return $this->end;
	}


	function setRemarks(string $remarks) {
		$this->remarks = $remarks;
	}


	function getRemarks(): string {
		return $this->remarks;
	}

}
