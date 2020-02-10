<?php

namespace CranachImport\Interfaces\Loaders;

require_once 'ILoader.php';


/**
 * Interface describing a concrete file import loader
 */
interface IFileLoader extends ILoader {

	function __construct(string $sourceFilePath);

}