<?php

namespace CranachDigitalArchive\Importer\Interfaces\Entities;

/**
 * Representing a generalized, language depending item
 */
interface ILanguageBaseItem extends IBaseItem
{
    public function setLangCode(string $langCode);

    public function getLangCode(): string;
}
