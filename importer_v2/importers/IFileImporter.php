<?php

namespace CranachImport\Importers;

require_once 'IImporter.php';


interface IFileImporter extends IImporter {

	function __construct(string $sourceFilePath);

}