<?php

require_once 'Language.php';
require_once 'process/Pipeline.php';
require_once 'collectors/GraphicsInventory.php';
require_once 'importers/GraphicsXMLImporter.php';
// require_once 'exporters/GraphicsJSONExporter.php';
// require_once 'exporters/GraphicsJSONLangExporter.php';
require_once 'exporters/GraphicsJSONLangExistenceTypeExporter.php';

use CranachImport\Process\Pipeline;
use CranachImport\Collectors\GraphicsInventory;
use CranachImport\Importers\GraphicsXMLImporter;
// use CranachImport\Exporters\GraphicsJSONExporter;
// use CranachImport\Exporters\GraphicsJSONLangExporter;
use CranachImport\Exporters\GraphicsJSONLangExistenceTypeExporter;

$graphicsXMLSourceFilePath = '../importer/import-file/20190328/CDA-GR_DatenÅbersicht_20190329.xml';
$graphicsJSONDestinationPath = './output/cda-graphics-v2.json';

$graphicsXmlImporter = new GraphicsXMLImporter($graphicsXMLSourceFilePath);
$graphicsJsonExporter = new GraphicsJSONLangExistenceTypeExporter($graphicsJSONDestinationPath);

$pipe = new Pipeline;
$pipe->addExporter($graphicsJsonExporter);

// $graphicsInventory = new GraphicsInventory;
// $pipe->addCollector($graphicsInventory);

$graphicsXmlImporter->registerPipeline($pipe);

$graphicsXmlImporter->start();
