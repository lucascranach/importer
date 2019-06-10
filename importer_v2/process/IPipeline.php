<?php

namespace CranachImport\Process;

require_once 'entities/IBaseItem.php';
require_once 'importers/IImporter.php';
require_once 'exporters/IExporter.php';

use CranachImport\Entities\IBaseItem;
use CranachImport\Importers\IImporter;
use CranachImport\Exporters\IExporter;


interface IPipeline {

	function addExporter(IExporter $exporter);

	function handleIncomingItem(IBaseItem $item);

	function handleEOF();

}