<?php

namespace CranachImport\Interfaces\Entities;

require_once 'IBaseItem.php';


/**
 * Representing a generalized, language depending item
 */
interface ILanguageBaseItem extends IBaseItem {

	function setLangCode(string $langCode);

	function getLangCode(): string;

}