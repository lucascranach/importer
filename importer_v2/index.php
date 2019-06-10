<?php

require_once 'Language.php';
require_once 'process/Pipeline.php';
require_once 'collectors/GraphicsInventory.php';
require_once 'importers/GraphicsXMLImporter.php';
require_once 'exporters/GraphicsJSONExporter.php';

use CranachImport\Process\Pipeline;
use CranachImport\Collectors\GraphicsInventory;
use CranachImport\Importers\GraphicsXMLImporter;
use CranachImport\Exporters\GraphicsJSONExporter;

$graphicsXMLSourceFilePath = '../importer/import-file/20190328/CDA-GR_DatenÅbersicht_20190329.xml';
$graphicsJSONDestinationPath = './cda-graphics-v2.json';

$graphicsXmlImporter = new GraphicsXMLImporter($graphicsXMLSourceFilePath);
$graphicsJsonExporter = new GraphicsJSONExporter($graphicsJSONDestinationPath);

$pipe = new Pipeline;
$pipe->addExporter($graphicsJsonExporter);

// $graphicsInventory = new GraphicsInventory;
// $pipe->addCollector($graphicsInventory);

$graphicsXmlImporter->registerPipeline($pipe);

$graphicsXmlImporter->start();
