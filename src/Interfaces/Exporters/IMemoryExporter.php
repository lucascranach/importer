<?php

namespace CranachDigitalArchive\Importer\Interfaces\Exporters;

/**
 * Interface describing a concrete memory exporter, to keep data in memory
 */
interface IMemoryExporter extends IExporter
{
    public function getData();
    public function cleanUp();
}
