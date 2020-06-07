<?php

namespace CranachDigitalArchive\Importer;

require_once __DIR__ . '/vendor/autoload.php';

use CranachDigitalArchive\Importer\Pipeline\Pipeline;

use CranachDigitalArchive\Importer\Modules\Graphics\Exporters\GraphicsJSONLangExistenceTypeExporter;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ConditionDeterminer;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\RemoteImageExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML\GraphicsLoader;
use CranachDigitalArchive\Importer\Modules\GraphicRestorations\Loaders\XML\GraphicRestorationsLoader;
use CranachDigitalArchive\Importer\Modules\GraphicRestorations\Exporters\GraphicRestorationsJSONExporter;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Loaders\XML\LiteratureReferencesLoader;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Exporters\LiteratureReferencesJSONExporter;
use CranachDigitalArchive\Importer\Modules\Paintings\Loaders\XML\PaintingsLoader;
use CranachDigitalArchive\Importer\Modules\Paintings\Exporters\PaintingsJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Loaders\XML\ThesaurusLoader;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ThesaurusJSONExporter;

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


$thesaurusLoader = ThesaurusLoader::withSourceAt('./input/20191122/CDA_Thesaurus_20191021.xml');
$thesaurusDestination = ThesaurusJSONExporter::withDestinationAt('./output/20191122/cda-thesaurus-v2.json');

$thesaurusLoader->pipe(
	$thesaurusDestination,
);

Pipeline::new()->withNodes(
	$thesaurusLoader,
	$thesaurusDestination,
)->start();