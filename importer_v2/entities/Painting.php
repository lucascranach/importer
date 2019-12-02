<?php

namespace CranachImport\Entities;

require_once 'ILanguageBaseItem.php';

use CranachImport\Entities\ILanguageBaseItem;


/**
 * Representing a single graphic and all its data
 * 	One instance containing only data for one language
 */
class Painting implements ILanguageBaseItem {

	public $langCode = '<unknown language>';


	function __construct() {

	}


	function setLangCode(string $langCode) {
		$this->langCode = $langCode;
	}


	function getLangCode(): string {
		return $this->langCode;
	}

}