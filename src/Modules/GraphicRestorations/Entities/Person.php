<?php

namespace CranachDigitalArchive\Importer\Modules\GraphicRestorations\Entities;


/**
 * Representing a single involved person in a graphic restoration
 */
class Person {

	public $role = '';
	public $name = '';


	function __construct()
	{

	}


	function setRole(string $role)
	{
		$this->role = $role;
	}


	function getRole(): string
	{
		return $this->role;
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