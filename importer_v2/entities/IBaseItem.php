<?php

namespace CranachImport\Entities;


interface IBaseItem {

	function setLangCode(string $langCode);

	function getLangCode(): string;

}