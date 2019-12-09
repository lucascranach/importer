<?php

namespace CranachImport\Entities\GraphicRestoration;


/**
 * Representing a single graphic restoration processing date and year set
 */
class ProcessingDates {

	public $beginDate = '';
	public $beginYear = null;
	public $endDate = '';
	public $endYear = null;


	function __construct() {

	}


	function setBeginDate(string $beginDate) {
		$this->beginDate = $beginDate;
	}


	function getBeginDate(): string {
		return $this->beginDate;
	}


	function setBeginYear(int $beginYear) {
		$this->beginYear = $beginYear;
	}


	function getBeginYear(): ?int {
		return $this->beginYear;
	}


	function setEndDate(string $endDate) {
		$this->endDate = $endDate;
	}


	function getEndDate(): string {
		return $this->endDate;
	}


	function setEndYear(int $endYear) {
		$this->endYear = $endYear;
	}


	function getEndYear(): ?int {
		return $this->endYear;
	}

}