<?php

namespace CranachImport\Entities;

require_once 'HistoricEventInformation.php';


class Dating {

	public $dated = null;
	public $begin = null;
	public $end = null;
	public $remark = '';
	public $historicEventInformations = [];


	function __construct() {

	}


	function setDated(int $dated) {
		$this->dated = $dated;
	}


	function getDated(): ?int {
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


	function setRemark(int $remark) {
		$this->remark = $remark;
	}


	function getRemark(): string {
		return $this->remark;
	}


	function addHistoricEventInformation(HistoricEventInformation $historicEventInformation) {
		$this->historicEventInformations[] = $historicEventInformation;
	}


	function getHistoricEventInformations(): array {
		return $this->historicEventInformations;
	}

}
