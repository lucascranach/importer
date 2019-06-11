<?php

namespace CranachImport\Entities;

require_once 'HistoricEventInformation.php';


class Dating {

	public $dated = '';
	public $begin = null;
	public $end = null;
	public $remarks = '';
	public $historicEventInformations = [];


	function __construct() {

	}


	function setDated(string $dated) {
		$this->dated = $dated;
	}


	function getDated(): string {
		return $this->dated;
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


	function setRemarks(string $remark) {
		$this->remarks = $remark;
	}


	function getRemarks(): string {
		return $this->remarks;
	}


	function addHistoricEventInformation(HistoricEventInformation $historicEventInformation) {
		$this->historicEventInformations[] = $historicEventInformation;
	}


	function getHistoricEventInformations(): array {
		return $this->historicEventInformations;
	}

}
