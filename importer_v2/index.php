<?php

require_once 'Language.php';
require_once 'process/Pipeline.php';
require_once 'importers/PaintingsXMLImporter.php';
require_once 'exporters/PaintingsJSONLangExistenceTypeExporter.php';
require_once 'importers/GraphicsXMLImporter.php';
require_once 'exporters/GraphicsJSONLangExistenceTypeExporter.php';
require_once 'importers/LiteratureReferencesXMLImporter.php';
require_once 'exporters/LiteratureReferencesJSONExporter.php';

use CranachImport\Process\Pipeline;
use CranachImport\Importers\PaintingsXMLImporter;
use CranachImport\Exporters\PaintingsJSONLangExistenceTypeExporter;
use CranachImport\Importers\GraphicsXMLImporter;
use CranachImport\Exporters\GraphicsJSONLangExistenceTypeExporter;
use CranachImport\Importers\LiteratureReferencesXMLImporter;
use CranachImport\Exporters\LiteratureReferencesJSONExporter;

function importPaintings() {
	$paintingsXMLSourceFilePaths = [
		'../import-file/20191122/CDA_Datenübersicht_P1_20191122.xml',
		'../import-file/20191122/CDA_Datenübersicht_P2_20191122.xml',
		'../import-file/20191122/CDA_Datenübersicht_P3_20191122.xml',
	];
	$paintingsJSONDestinationPath = './output/20191122/cda-paintings-v2.json';

	$paintingsXmlImporter = new PaintingsXMLImporter($paintingsXMLSourceFilePaths);
	$paintingsJsonExporter = new PaintingsJSONLangExistenceTypeExporter($paintingsJSONDestinationPath);

	$pipe = new Pipeline;
	$pipe->addExporter($paintingsJsonExporter);

	$paintingsXmlImporter->registerPipeline($pipe);

	$paintingsXmlImporter->start();
}

function importGraphics() {
	$graphicsXMLSourceFilePath = '../import-file/20191122/CDA-GR_Datenuebersicht_20191122.xml';
	$graphicsJSONDestinationPath = './output/20191122/cda-graphics-v2.json';

	$graphicsXmlImporter = new GraphicsXMLImporter($graphicsXMLSourceFilePath);
	$graphicsJsonExporter = new GraphicsJSONLangExistenceTypeExporter($graphicsJSONDestinationPath);

	$pipe = new Pipeline;
	$pipe->addExporter($graphicsJsonExporter);

	$graphicsXmlImporter->registerPipeline($pipe);

	$graphicsXmlImporter->start();
}

function importLiteratureReferences() {
	$literatureReferencesXMLSourceFilePath = '../import-file/20191122/CDA-GR_RestDokumente_20191122.xml';
	$literatureReferencesJSONDestinationPath = './output/20191122/cda-literaturereferences-v2.json';

	$literatureReferencesXmlImporter = new LiteratureReferencesXMLImporter($literatureReferencesXMLSourceFilePath);
	$literatureReferencesJsonExporter = new LiteratureReferencesJSONExporter($literatureReferencesJSONDestinationPath);

	$pipe = new Pipeline;
	$pipe->addExporter($literatureReferencesJsonExporter);

	$literatureReferencesXmlImporter->registerPipeline($pipe);

	$literatureReferencesXmlImporter->start();
}

importPaintings();
// importGraphics();
// importLiteratureReferences();