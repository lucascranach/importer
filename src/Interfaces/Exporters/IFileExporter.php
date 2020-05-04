<?php

namespace CranachDigitalArchive\Importer\Interfaces\Exporters;


/**
 * Interface describing a concrete file exporter
 */
interface IFileExporter extends IExporter {

	function __construct(string $destFilepath);

}