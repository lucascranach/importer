<?php

namespace CranachDigitalArchive\Importer\Interfaces\Entities;


/**
 * Representing a generalized, language depending item
 */
interface ILanguageBaseItem extends IBaseItem {

	function setLangCode(string $langCode);

	function getLangCode(): string;

}