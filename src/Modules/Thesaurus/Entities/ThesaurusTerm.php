<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus\Entities;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;

/**
 * Representing a single thesaurus term
 */
class ThesaurusTerm
{
	public $term = '';
	public $alt = [];
	public $subTerms = [];


	public function __construct()
	{
	}


	public function setTerm(string $term)
	{
		$this->term = $term;
	}


	public function getTerm(string $term)
	{
		return $this->term;
	}


	public function addAlt(string $key, $value)
	{
		$this->alt[$key] = $value;
	}


	public function getAlts(): array
	{
		return $this->alt;
	}


	public function addSubTerm(ThesaurusTerm $term)
	{
		$this->subTerms[] = $term;
	}


	public function getSubTerms(): array
	{
		return $this->subTerms;
	}
}
