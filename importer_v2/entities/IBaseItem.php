<?php

namespace CranachImport\Entities;


/**
 * Representing a generalized item
 */
interface IBaseItem {

	function setLangCode(string $langCode);

	function getLangCode(): string;

}