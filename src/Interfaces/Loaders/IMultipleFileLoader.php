<?php

namespace CranachDigitalArchive\Importer\Interfaces\Loaders;

/**
 * Interface describing a multiple file import loader
 */
interface IMultipleFileLoader extends ILoader
{
    public static function withSourcesAt(array $sourceFilePaths);
}
