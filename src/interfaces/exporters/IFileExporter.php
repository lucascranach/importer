<?php

namespace CranachImport\Interfaces\Exporters;

require_once 'IExporter.php';


/**
 * Interface describing a concrete file exporter
 */
interface IFileExporter extends IExporter {

	function __construct(string $destFilepath);

}