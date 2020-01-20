<?php

require_once 'Language.php';
require_once 'process/Pipeline.php';

require_once 'jobs/xml/PaintingsJob.php';
require_once 'exporters/PaintingsJSONLangExporter.php';

require_once 'jobs/xml/GraphicsJob.php';
require_once 'exporters/GraphicsJSONLangExistenceTypeExporter.php';
require_once 'postProcessors/graphic/RemoteImageExistenceChecker.php';
require_once 'postProcessors/graphic/ConditionDeterminer.php';

require_once 'jobs/xml/GraphicsRestorationJob.php';
require_once 'exporters/GraphicsRestorationJSONExporter.php';

require_once 'jobs/xml/LiteratureReferencesJob.php';
require_once 'exporters/LiteratureReferencesJSONExporter.php';

use CranachImport\Process\Pipeline;

use CranachImport\Jobs\XML\PaintingsJob;
use CranachImport\Exporters\PaintingsJSONLangExporter;

use CranachImport\Jobs\XML\GraphicsJob;
use CranachImport\Exporters\GraphicsJSONLangExistenceTypeExporter;
use CranachImport\PostProcessors\Graphic\RemoteImageExistenceChecker;
use CranachImport\PostProcessors\Graphic\ConditionDeterminer;

use CranachImport\Jobs\XML\GraphicsRestorationJob;
use CranachImport\Exporters\GraphicsRestorationJSONExporter;

use CranachImport\Jobs\XML\LiteratureReferencesJob;
use CranachImport\Exporters\LiteratureReferencesJSONExporter;

/* @TODO: Use better determination and handling of source- and destination-paths */

function importPaintings() {
	$paintingsXMLSourceFilePaths = [
		'../input/20191122/CDA_Datenübersicht_P1_20191122.xml',
		'../input/20191122/CDA_Datenübersicht_P2_20191122.xml',
		'../input/20191122/CDA_Datenübersicht_P3_20191122.xml',
	];
	$paintingsJSONDestinationPath = '../output/20191122/cda-paintings-v2.json';

	$paintingsXmlJob = new PaintingsJob($paintingsXMLSourceFilePaths);
	$paintingsJsonExporter = new PaintingsJSONLangExporter($paintingsJSONDestinationPath);

	$pipe = new Pipeline;
	$pipe->addExporter($paintingsJsonExporter);

	$paintingsXmlJob->registerPipeline($pipe);

	$paintingsXmlJob->start();
}

function importGraphics() {
	$graphicsXMLSourceFilePath = '../input/20191122/CDA-GR_Datenuebersicht_20191122.xml';
	$graphicsJSONDestinationPath = '../output/20191122/cda-graphics-v2.json';

	$graphicsXmlJob = new GraphicsJob($graphicsXMLSourceFilePath);
	$graphicsJsonExporter = new GraphicsJSONLangExistenceTypeExporter($graphicsJSONDestinationPath);
	$graphicRemoteImageExitenceChecker = new RemoteImageExistenceChecker('../.cache');
	$graphicConditionDeterminer = new ConditionDeterminer();

	$pipe = new Pipeline;
	$pipe->addExporter($graphicsJsonExporter);
	$pipe->addPostProcessors([
		$graphicRemoteImageExitenceChecker,
		$graphicConditionDeterminer,
	]);

	$graphicsXmlJob->registerPipeline($pipe);

	$graphicsXmlJob->start();
}

function importGraphicsRestoration() {
	$graphicsRestorationXMLSourceFilePath = '../input/20191122/CDA-GR_RestDokumente_20191122.xml';
	$graphicsRestorationJSONDestinationPath = '../output/20191122/cda-graphics-restoration-v2.json';

	$graphicsRestorationXmlJob = new GraphicsRestorationJob(
		$graphicsRestorationXMLSourceFilePath,
	);
	$graphicsRestorationJsonExporter = new GraphicsRestorationJSONExporter(
		$graphicsRestorationJSONDestinationPath,
	);

	$pipe = new Pipeline;
	$pipe->addExporter($graphicsRestorationJsonExporter);

	$graphicsRestorationXmlJob->registerPipeline($pipe);

	$graphicsRestorationXmlJob->start();
}

function importLiteratureReferences() {
	$literatureReferencesXMLSourceFilePath = '../input/20191122/CDA_Literaturverweise_20191122.xml';
	$literatureReferencesJSONDestinationPath = '../output/20191122/cda-literaturereferences-v2.json';

	$literatureReferencesXmlJob = new LiteratureReferencesJob($literatureReferencesXMLSourceFilePath);
	$literatureReferencesJsonExporter = new LiteratureReferencesJSONExporter($literatureReferencesJSONDestinationPath);

	$pipe = new Pipeline;
	$pipe->addExporter($literatureReferencesJsonExporter);

	$literatureReferencesXmlJob->registerPipeline($pipe);

	$literatureReferencesXmlJob->start();
}

importPaintings();
importGraphics();
importGraphicsRestoration();
importLiteratureReferences();