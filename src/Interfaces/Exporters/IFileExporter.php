<?php

namespace CranachDigitalArchive\Importer\Interfaces\Exporters;


/**
 * Interface describing a concrete file exporter
 */
interface IFileExporter extends IExporter
{

	public static function withDestinationAt(string $destFilepath);

}
