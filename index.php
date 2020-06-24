<?php

namespace CranachDigitalArchive\Importer;

require_once __DIR__ . '/vendor/autoload.php';

use CranachDigitalArchive\Importer\Pipeline\Pipeline;

use CranachDigitalArchive\Importer\Modules\Graphics\Exporters\GraphicsJSONLangExistenceTypeExporter;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ConditionDeterminer;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\RemoteImageExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML\GraphicsLoader;
use CranachDigitalArchive\Importer\Modules\Restorations\Loaders\XML\RestorationsLoader;
use CranachDigitalArchive\Importer\Modules\Restorations\Exporters\RestorationsJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Loaders\XML\LiteratureReferencesLoader;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Exporters\LiteratureReferencesJSONExporter;
use CranachDigitalArchive\Importer\Modules\Paintings\Loaders\XML\PaintingsLoader;
use CranachDigitalArchive\Importer\Modules\Paintings\Exporters\PaintingsJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Paintings\Exporters\PaintingsElasticsearchLangExporter;
use CranachDigitalArchive\Importer\Modules\Archivals\Loaders\XML\ArchivalsLoader;
use CranachDigitalArchive\Importer\Modules\Archivals\Exporters\ArchivalsJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Loaders\XML\ThesaurusLoader;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ThesaurusJSONExporter;


/* Paintings */
$paintingsLoader = PaintingsLoader::withSourcesAt([
    './input/20191122/CDA_Datenübersicht_P1_20191122.xml',
    './input/20191122/CDA_Datenübersicht_P2_20191122.xml',
    './input/20191122/CDA_Datenübersicht_P3_20191122.xml',
]);
$paintingsDestination = PaintingsJSONLangExporter::withDestinationAt(
    './output/20191122/cda-paintings-v2.json',
);
$paintingsElasticsearchBulkDestination = PaintingsElasticsearchLangExporter::withDestinationAt(
    './output/20191122/elasticsarch/cda-paintings-v2.bulk',
);

$paintingsLoader->pipe(
    $paintingsDestination,
);
$paintingsLoader->pipe(
    $paintingsElasticsearchBulkDestination,
);


/* PaintingsRestorations */
$paintingRestorationsLoader = RestorationsLoader::withSourcesAt([
    './input/20191122/CDA_RestDukomente_P1_20191122.xml',
    './input/20191122/CDA_RestDokumente_P2_20191122.xml',
    './input/20191122/CDA_RestDokumente_P3_20191122.xml',
]);
$paintingRestorationsDestination = RestorationsJSONLangExporter::withDestinationAt(
    './output/20191122/cda-paintings-restoration-v2.json',
);

$paintingRestorationsLoader->pipe(
    $paintingRestorationsDestination,
);


/* Graphics */
$graphicsLoader = GraphicsLoader::withSourceAt(
    './input/20191122/CDA-GR_Datenuebersicht_20191122.xml',
);
$remoteImageExistenceChecker = RemoteImageExistenceChecker::withCacheAt('./.cache');
$conditionDeterminer = ConditionDeterminer::new();
$graphicsDestination = GraphicsJSONLangExistenceTypeExporter::withDestinationAt(
    './output/20191122/cda-graphics-v2.json',
);

$graphicsLoader->pipe(
    $remoteImageExistenceChecker,
    $conditionDeterminer,
    $graphicsDestination,
);


/* GraphicRestorations */
$graphicRestorationsLoader = RestorationsLoader::withSourcesAt([
    './input/20191122/CDA-GR_RestDokumente_20191122.xml',
]);
$graphicRestorationsDestination = RestorationsJSONLangExporter::withDestinationAt(
    './output/20191122/cda-graphics-restoration-v2.json',
);

$graphicRestorationsLoader->pipe(
    $graphicRestorationsDestination,
);


/* LiteratureReferences */
$literatureReferencesLoader = LiteratureReferencesLoader::withSourceAt(
    './input/20191122/CDA_Literaturverweise_20191122.xml',
);
$literatureReferencesDestination = LiteratureReferencesJSONExporter::withDestinationAt(
    './output/20191122/cda-literaturereferences-v2.json',
);

$literatureReferencesLoader->pipe(
    $literatureReferencesDestination,
);


/* Archivals */
$archivalsLoader = ArchivalsLoader::withSourceAt(
    './input/20191122/CDA-A_Datenübersicht_20191122.xml',
);
$archivalsDestination = ArchivalsJSONLangExporter::withDestinationAt(
    './output/20191122/cda-archivals-v2.json',
);

$archivalsLoader->pipe(
    $archivalsDestination,
);


/* Thesaurus */
$thesaurusLoader = ThesaurusLoader::withSourceAt(
    './input/20191122/CDA_Thesaurus_20191021.xml'
);
$thesaurusDestination = ThesaurusJSONExporter::withDestinationAt(
    './output/20191122/cda-thesaurus-v2.json',
);

$thesaurusLoader->pipe(
    $thesaurusDestination,
);



/* Pipeline */

Pipeline::new()->withNodes(
    /* Paintings */
    $paintingsLoader,
    $paintingsDestination,
    $paintingsElasticsearchBulkDestination,

    /* PaintingRestorations */
    $paintingRestorationsLoader,
    $paintingRestorationsDestination,

    /* Graphics */
    $graphicsLoader,
    $remoteImageExistenceChecker,
    $conditionDeterminer,
    $graphicsDestination,

    /* GraphicRestorations */
    $graphicRestorationsLoader,
    $graphicRestorationsDestination,

    /* LiteratureReferences */
    $literatureReferencesLoader,
    $literatureReferencesDestination,

    /* Archivals */
    $archivalsLoader,
    $archivalsDestination,

    /* Thesaurus */
    $thesaurusLoader,
    $thesaurusDestination,
)->start();
