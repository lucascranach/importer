<?php

namespace CranachImport\Entities\GraphicRestoration;

require_once 'Test.php';
require_once 'Person.php';
require_once 'ProcessingDates.php';
require_once 'Signature.php';



/**
 * Representing a single graphic restoration survey
 */
class Survey {

	public $type = '';
	public $project = '';
	public $overallAnalysis = '';
	public $remarks = '';
	public $tests = [];
	public $involvedPersons = [];
	public $processingDates = null;
	public $signature = null;


	function __construct() {

	}


	function setType(string $type) {
		$this->type = $type;
	}


	function getType(): string {
		return $this->type;
	}


	function setProject(string $project) {
		$this->project = $project;
	}


	function getProject(): string {
		return $this->project;
	}


	function setOverallAnalysis(string $overallAnalysis) {
		$this->overallAnalysis = $overallAnalysis;
	}


	function getOverallAnalysis(): string {
		return $this->overallAnalysis;
	}


	function setRemarks(string $remarks) {
		$this->remarks = $remarks;
	}


	function getRemarks(): string {
		return $this->remarks;
	}


	function addTest(Test $test) {
		$this->tests[] = $test;
	}


	function getTests(): array {
		return $this->tests;
	}


	function addInvolvedPerson(Person $involvedPerson) {
		$this->involvedPersons[] = $involvedPerson;
	}


	function getInvolvedPersons(): array {
		return $this->involvedPersons;
	}


	function setProcessingDates(ProcessingDates $processingDates) {
		$this->processingDates = $processingDates;
	}


	function getProcessingDates(): ProcessingDates {
		return $this->processingDates;
	}


	function setSignature(Signature $signature) {
		$this->signature = $signature;
	}


	function getSignature(): Signature {
		return $this->signature;
	}

}