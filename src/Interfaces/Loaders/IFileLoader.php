<?php

namespace CranachDigitalArchive\Importer\Interfaces\Loaders;

/**
 * Interface describing a concrete file import loader
 */
interface IFileLoader extends ILoader
{
    public static function withSourceAt(string $sourceFilePath);
}
