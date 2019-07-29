<?php

require_once 'Language.php';
require_once 'process/Pipeline.php';
require_once 'importers/GraphicsXMLImporter.php';
require_once 'exporters/GraphicsJSONLangExistenceTypeExporter.php';
require_once 'importers/LiteratureReferencesXMLImporter.php';
require_once 'exporters/LiteratureReferencesJSONExporter.php';

use CranachImport\Process\Pipeline;
use CranachImport\Importers\GraphicsXMLImporter;
use CranachImport\Exporters\GraphicsJSONLangExistenceTypeExporter;
use CranachImport\Importers\LiteratureReferencesXMLImporter;
use CranachImport\Exporters\LiteratureReferencesJSONExporter;


function importGraphics() {
	$graphicsXMLSourceFilePath = '../import-file/20190712/CDA-G_MiniExport_20190712.xml';
	$graphicsJSONDestinationPath = './output/20190712/cda-graphics-v2.json';

	$graphicsXmlImporter = new GraphicsXMLImporter($graphicsXMLSourceFilePath);
	$graphicsJsonExporter = new GraphicsJSONLangExistenceTypeExporter($graphicsJSONDestinationPath);

	$pipe = new Pipeline;
	$pipe->addExporter($graphicsJsonExporter);

	$graphicsXmlImporter->registerPipeline($pipe);

	$graphicsXmlImporter->start();
}

function importLiteratureReferences() {
	$literatureReferencesXMLSourceFilePath = '../import-file/20190712/CDA_Literaturverweise_20190712.xml';
	$literatureReferencesJSONDestinationPath = './output/20190712/cda-literaturereferences-v2.json';

	$literatureReferencesXmlImporter = new LiteratureReferencesXMLImporter($literatureReferencesXMLSourceFilePath);
	$literatureReferencesJsonExporter = new LiteratureReferencesJSONExporter($literatureReferencesJSONDestinationPath);

	$pipe = new Pipeline;
	$pipe->addExporter($literatureReferencesJsonExporter);

	$literatureReferencesXmlImporter->registerPipeline($pipe);

	$literatureReferencesXmlImporter->start();
}


importGraphics();
importLiteratureReferences();