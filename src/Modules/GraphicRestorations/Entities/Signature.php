<?php

namespace CranachDigitalArchive\Importer\Modules\GraphicRestorations\Entities;


/**
 * Representing a single signature
 */
class Signature {

	public $date = '';
	public $name = '';


	function __construct()
	{

	}


	function setDate(string $date)
	{
		$this->date = $date;
	}


	function getDate(): string
	{
		return $this->date;
	}


	function setName(string $name)
	{
		$this->name = $name;
	}


	function getName(): string
	{
		return $this->name;
	}

}