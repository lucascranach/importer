<?php

namespace CranachDigitalArchive\Importer;

ini_set('memory_limit', '2048M');
echo "MemoryLimit: " . ini_get('memory_limit') . "\n\n";

require_once __DIR__ . '/vendor/autoload.php';

use CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML\GraphicsLoader;
use CranachDigitalArchive\Importer\Modules\Graphics\Exporters\GraphicsJSONLangExistenceTypeExporter;
use CranachDigitalArchive\Importer\Modules\Graphics\Exporters\GraphicsElasticsearchLangExporter;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ConditionDeterminer;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithThesaurus as GraphicsExtenderWithThesaurus;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithRestorations as GraphicsExtenderWithRestorations;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\RemoteImageExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Restorations\Loaders\XML\RestorationsLoader;
use CranachDigitalArchive\Importer\Modules\Restorations\Exporters\RestorationsMemoryExporter;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Loaders\XML\LiteratureReferencesLoader;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Exporters\LiteratureReferencesJSONExporter;
use CranachDigitalArchive\Importer\Modules\Paintings\Loaders\XML\PaintingsLoader;
use CranachDigitalArchive\Importer\Modules\Paintings\Exporters\PaintingsJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Paintings\Exporters\PaintingsElasticsearchLangExporter;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithThesaurus as PaintingsExtenderWithThesaurus;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithRestorations as PaintingsExtenderWithRestorations;
use CranachDigitalArchive\Importer\Modules\Archivals\Loaders\XML\ArchivalsLoader;
use CranachDigitalArchive\Importer\Modules\Archivals\Exporters\ArchivalsJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Archivals\Exporters\ArchivalsElasticsearchLangExporter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Loaders\XML\ThesaurusLoader;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ThesaurusJSONExporter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ThesaurusMemoryExporter;

$date = '20210305';
$inputDirectory = './input/' . $date;
$destDirectory = './docs/' . $date;


/* Inputfiles */
$thesaurusInputFilepath = $inputDirectory . '/CDA_Thesaurus_' . $date . '.xml';
$paintingsRestorationInputFilepaths = [
    $inputDirectory . '/CDA_RestDokumente_P1_' . $date . '.xml',
    $inputDirectory . '/CDA_RestDokumente_P2_' . $date . '.xml',
    $inputDirectory . '/CDA_RestDokumente_P3_' . $date . '.xml',
];
$paintingsInputFilepaths = [
    $inputDirectory . '/CDA_Datenübersicht_P1_' . $date . '.xml',
    $inputDirectory . '/CDA_Datenübersicht_P2_' . $date . '.xml',
    $inputDirectory . '/CDA_Datenübersicht_P3_' . $date . '.xml',
];
$graphicsRestorationInputFilepaths = [
    $inputDirectory . '/CDA-GR_RestDokumente_' . $date . '.xml',
];
$graphicsInputFilepath = $inputDirectory . '/CDA-GR_Datenübersicht_' . $date . '.xml';
$literatureInputFilepaths = [
    $inputDirectory . '/CDA_Literaturverweise_P1_' . $date . '.xml',
    $inputDirectory . '/CDA_Literaturverweise_P2_' . $date . '.xml',
];
$archivalsInputFilepath = $inputDirectory . '/CDA-A_Datenübersicht_' . $date . '.xml';


/* Outputfiles */
$thesaurusOutputFilepath = $destDirectory . '/cda-thesaurus-v2.json';
$paintingsOutputFilepath = $destDirectory . '/cda-paintings-v2.json';
$paintingsElasticsearchOutputFilepath = $destDirectory . '/elasticsearch/cda-paintings-v2.bulk';
$graphicsOutputFilepath = $destDirectory . '/cda-graphics-v2.json';
$graphicsElasticsearchOutputFilepath = $destDirectory . '/elasticsearch/cda-graphics-v2.bulk';
$literatureReferenceOutputFilepath = $destDirectory . '/cda-literaturereferences-v2.json';
$archivalsOutputFilepath = $destDirectory . '/cda-archivals-v2.json';
$archivalsElasticsearchOutputFilepath = $destDirectory . '/elasticsearch/cda-archivals-v2.bulk';



/* Thesaurus */
$thesaurusMemoryDestination = ThesaurusMemoryExporter::new(); /* needed later for graphics and paintings */

ThesaurusLoader::withSourceAt($thesaurusInputFilepath)->pipe(
    ThesaurusJSONExporter::withDestinationAt($thesaurusOutputFilepath),
    $thesaurusMemoryDestination,
)->run(); /* and we have to run it directly */


/* PaintingsRestorations */
$paintingsRestorationMemoryDestination = RestorationsMemoryExporter::new();

RestorationsLoader::withSourcesAt($paintingsRestorationInputFilepaths)->pipe(
    $paintingsRestorationMemoryDestination,
)->run(); /* and we have to run it directly */


/* Paintings */
$paintingsRemoteImageExistenceChecker = RemoteImageExistenceChecker::withCacheAt(
    './.cache',
    RemoteImageExistenceChecker::REPRESENTATIVE,
    'remotePaintingsImageExistenceChecker'
);
$paintingsRestorationExtender = PaintingsExtenderWithRestorations::new($paintingsRestorationMemoryDestination);
$paintingsThesaurusExtender = PaintingsExtenderWithThesaurus::new($thesaurusMemoryDestination);
$paintingsDestination = PaintingsJSONLangExporter::withDestinationAt($paintingsOutputFilepath);
$paintingsElasticsearchBulkDestination = PaintingsElasticsearchLangExporter::withDestinationAt(
    $paintingsElasticsearchOutputFilepath
);

$paintingsLoader = PaintingsLoader::withSourcesAt($paintingsInputFilepaths)->pipe(
    $paintingsRemoteImageExistenceChecker->pipe(
        $paintingsRestorationExtender->pipe(
            $paintingsDestination,
            $paintingsThesaurusExtender->pipe(
                $paintingsElasticsearchBulkDestination,
            ),
        ),
    ),
);


/* GraphicRestorations */
$graphicsRestorationMemoryDestination = RestorationsMemoryExporter::new();

RestorationsLoader::withSourcesAt($graphicsRestorationInputFilepaths)->pipe(
    $graphicsRestorationMemoryDestination,
)->run(); /* and we have to run it directly */


/* Graphics */
$graphicsRemoteImageExistenceChecker = RemoteImageExistenceChecker::withCacheAt(
    './.cache',
    function ($item) {
        /* We want the representative images for virtual objects */
        return $item->getIsVirtual()
            ? RemoteImageExistenceChecker::REPRESENTATIVE
            : RemoteImageExistenceChecker::OVERALL;
    },
    'remoteGraphicsImageExistenceChecker'
);
$graphicsConditionDeterminer = ConditionDeterminer::new();
$graphicsRestorationExtender = GraphicsExtenderWithRestorations::new($graphicsRestorationMemoryDestination);
$graphicsThesaurusExtender = GraphicsExtenderWithThesaurus::new($thesaurusMemoryDestination);
$graphicsDestination = GraphicsJSONLangExistenceTypeExporter::withDestinationAt($graphicsOutputFilepath);
$graphicsElasticsearchBulkDestination = GraphicsElasticsearchLangExporter::withDestinationAt(
    $graphicsElasticsearchOutputFilepath
);

$graphicsLoader = GraphicsLoader::withSourceAt($graphicsInputFilepath)->pipe(
    $graphicsRemoteImageExistenceChecker->pipe(
        $graphicsConditionDeterminer->pipe(
            $graphicsRestorationExtender->pipe(
                $graphicsDestination,
                $graphicsThesaurusExtender->pipe(
                    $graphicsElasticsearchBulkDestination,
                ),
            ),
        ),
    ),
);


/* LiteratureReferences */
$literatureReferencesLoader = LiteratureReferencesLoader::withSourcesAt($literatureInputFilepaths)->pipe(
    LiteratureReferencesJSONExporter::withDestinationAt($literatureReferenceOutputFilepath),
);


/* Archivals */
$archivalsDestination = ArchivalsJSONLangExporter::withDestinationAt($archivalsOutputFilepath);

$archivalsElasticsearchBulkDestination = ArchivalsElasticsearchLangExporter::withDestinationAt(
    $archivalsElasticsearchOutputFilepath
);

$archivalsLoader = ArchivalsLoader::withSourceAt($archivalsInputFilepath)->pipe(
    $archivalsDestination,
    $archivalsElasticsearchBulkDestination,
);


/* Trigger loaders and final exit routines */
$loaders = [
    $paintingsLoader,
    $graphicsLoader,
    $literatureReferencesLoader,
    $archivalsLoader,
];

foreach ($loaders as $loader) {
    $loader->run();
}

$thesaurusMemoryDestination->cleanUp();
$paintingsRestorationMemoryDestination->cleanUp();
$graphicsRestorationMemoryDestination->cleanUp();
