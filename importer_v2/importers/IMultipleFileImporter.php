<?php

namespace CranachImport\Importers;

require_once 'IImporter.php';


/**
 * Interface describing a concrete file importer
 */
interface IMultipleFileImporter extends IImporter {

	function __construct(array $sourceFilePaths);

}