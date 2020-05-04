<?php

namespace CranachDigitalArchive\Importer\Interfaces\Loaders;


/**
 * Interface describing a concrete file import loader
 */
interface IFileLoader extends ILoader {

	function __construct(string $sourceFilePath);

}