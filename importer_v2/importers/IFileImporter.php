<?php

namespace CranachImport\Importers;

require_once 'IImporter.php';


/**
 * Interface describing a concrete file importer
 */
interface IFileImporter extends IImporter {

	function __construct(string $sourceFilePath);

}