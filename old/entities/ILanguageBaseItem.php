<?php

namespace CranachImport\Entities;

require_once('IBaseItem.php');

use CranachImport\Entities\IBaseItem;

/**
 * Representing a generalized, language depending item
 */
interface ILanguageBaseItem extends IBaseItem {

	function setLangCode(string $langCode);

	function getLangCode(): string;

}