<?php

namespace CranachDigitalArchive\Importer;

require_once __DIR__ . '/vendor/autoload.php';

use CranachDigitalArchive\Importer\Pipeline\Pipeline;

use CranachDigitalArchive\Importer\Modules\{
	Graphics\Exporters\GraphicsJSONLangExistenceTypeExporter,
	Graphics\Transformers\ConditionDeterminer,
	Graphics\Transformers\RemoteImageExistenceChecker,
	Graphics\Loaders\XML\GraphicsLoader,

	GraphicRestorations\Loaders\XML\GraphicRestorationsLoader,
	GraphicRestorations\Exporters\GraphicRestorationsJSONExporter,

	LiteratureReferences\Loaders\XML\LiteratureReferencesLoader,
	LiteratureReferences\Exporters\LiteratureReferencesJSONExporter,

	Paintings\Loaders\XML\PaintingsLoader,
	Paintings\Exporters\PaintingsJSONLangExporter,
};

/* Graphics */
$graphicsLoader = GraphicsLoader::withSourceAt('./input/20191122/CDA-GR_Datenuebersicht_20191122.xml');
$remoteImageExistenceChecker = RemoteImageExistenceChecker::withCacheAt('./.cache');
$conditionDeterminer = ConditionDeterminer::new();
$graphicsDestination = GraphicsJSONLangExistenceTypeExporter::withDestinationAt('./output/20191122/cda-graphics-v2.json');

$graphicsLoader->pipe(
	$remoteImageExistenceChecker,
	$conditionDeterminer,
	$graphicsDestination,
);

Pipeline::new()->withNodes(
	$graphicsLoader,
	$remoteImageExistenceChecker,
	$conditionDeterminer,
	$graphicsDestination,
)->start();


/* Graphics restoration */
$graphicRestorationsLoader = GraphicRestorationsLoader::withSourceAt('./input/20191122/CDA-GR_RestDokumente_20191122.xml');
$graphicRestorationsDestination = GraphicRestorationsJSONExporter::withDestinationAt('./output/20191122/cda-graphics-restoration-v2.json');

$graphicRestorationsLoader->pipe(
	$graphicRestorationsDestination,
);

Pipeline::new()->withNodes(
	$graphicRestorationsLoader,
	$graphicRestorationsDestination,
)->start();


/* Literature references */
$literatureReferencesLoader = LiteratureReferencesLoader::withSourceAt('./input/20191122/CDA_Literaturverweise_20191122.xml');
$literatureReferencesDestination = LiteratureReferencesJSONExporter::withDestinationAt('./output/20191122/cda-literaturereferences-v2.json');

$literatureReferencesLoader->pipe(
	$literatureReferencesDestination,
);

Pipeline::new()->withNodes(
	$literatureReferencesLoader,
	$literatureReferencesDestination,
)->start();


/* Paintings */
$paintingsLoader = PaintingsLoader::withSourcesAt([
	'./input/20191122/CDA_Datenübersicht_P1_20191122.xml',
	'./input/20191122/CDA_Datenübersicht_P2_20191122.xml',
	'./input/20191122/CDA_Datenübersicht_P3_20191122.xml',
]);
$paintingsDestination = PaintingsJSONLangExporter::withDestinationAt('./output/20191122/cda-paintings-v2.json');

$paintingsLoader->pipe(
	$paintingsDestination,
);

Pipeline::new()->withNodes(
	$paintingsLoader,
	$paintingsDestination,
)->start();
