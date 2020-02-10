<?php

namespace CranachImport\Interfaces\Loaders;

require_once 'ILoader.php';


/**
 * Interface describing a multiple file import loader
 */
interface IMultipleFileLoader extends ILoader {

	function __construct(array $sourceFilePaths);

}