<?php

namespace CranachDigitalArchive\Importer;

ini_set('memory_limit', '2048M');
echo "MemoryLimit: " . ini_get('memory_limit') . "\n\n";

require_once __DIR__ . '/vendor/autoload.php';

use CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML\GraphicsLoader;
use CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML\GraphicsPreLoader;
use CranachDigitalArchive\Importer\Modules\Graphics\Collectors\LocationsCollector as GraphicsLocationsCollector;
use CranachDigitalArchive\Importer\Modules\Graphics\Collectors\RepositoriesCollector as GraphicsRepositoriesCollector;
use CranachDigitalArchive\Importer\Modules\Graphics\Exporters\GraphicsJSONLangExistenceTypeExporter;
use CranachDigitalArchive\Importer\Modules\Graphics\Exporters\GraphicsElasticsearchLangExporter;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ConditionDeterminer;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\MapToSearchableGraphic;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithThesaurus as GraphicsExtenderWithThesaurus;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithBasicFilterValues as GraphicsExtenderWithBasicFilterValues;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithInvolvedPersonsFullnames as GraphicsExtenderWithInvolvedPersonsFullnames;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithSortingInfo as GraphicsExtenderWithSortingInfo;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithIds as GraphicsExtenderWithIds;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithRestorations as GraphicsExtenderWithRestorations;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithLocations as GraphicsExtenderWithLocations;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithRepositories as GraphicsExtenderWithRepositories;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\MetadataFiller as GraphicsMetadataFiller;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\RemoteImageExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\RemoteDocumentExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Main\Collectors\MetaReferenceCollector;
use CranachDigitalArchive\Importer\Modules\Main\Gates\SkipSoftDeletedArtefactGate;
use CranachDigitalArchive\Importer\Modules\Restorations\Loaders\XML\RestorationsLoader;
use CranachDigitalArchive\Importer\Modules\Restorations\Exporters\RestorationsMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Restorations\Transformers\ExtenderWithIds as RestorationsExtenderWithIds;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Loaders\XML\LiteratureReferencesLoader;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Exporters\LiteratureReferencesJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Exporters\LiteratureReferencesElasticsearchLangExporter;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers\MetadataFiller as LiteratureReferencesMetadataFiller;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers\ExtenderWithAggregatedData;
use CranachDigitalArchive\Importer\Modules\Paintings\Loaders\XML\PaintingsLoader;
use CranachDigitalArchive\Importer\Modules\Paintings\Loaders\XML\PaintingsPreLoader;
use CranachDigitalArchive\Importer\Modules\Paintings\Collectors\ReferencesCollector as PaintingsReferencesCollector;
use CranachDigitalArchive\Importer\Modules\Paintings\Exporters\PaintingsJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Paintings\Exporters\PaintingsElasticsearchLangExporter;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\MapToSearchablePainting;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithThesaurus as PaintingsExtenderWithThesaurus;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithBasicFilterValues as PaintingsExtenderWithBasicFilterValues;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithInvolvedPersonsFullnames as PaintingsExtenderWithInvolvedPersonsFullnames;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithSortingInfo as PaintingsExtenderWithSortingInfo;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithIds as PaintingsExtenderWithIds;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithRestorations as PaintingsExtenderWithRestorations;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithReferences as PaintingsExtenderWithReferences;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\MetadataFiller as PaintingsMetadataFiller;
use CranachDigitalArchive\Importer\Modules\Archivals\Loaders\XML\ArchivalsLoader;
use CranachDigitalArchive\Importer\Modules\Archivals\Exporters\ArchivalsJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Archivals\Exporters\ArchivalsElasticsearchLangExporter;
use CranachDigitalArchive\Importer\Modules\Archivals\Transformers\MetadataFiller as ArchivalsMetadataFiller;
use CranachDigitalArchive\Importer\Modules\Archivals\Transformers\MapToSearchableArchival;
use CranachDigitalArchive\Importer\Modules\Archivals\Transformers\ExtenderWithRepositoryId;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Loaders\XML\ThesaurusLoader as ThesaurusXMLLoader;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Loaders\Memory\ThesaurusLoader as ThesaurusMemoryLoader;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ThesaurusJSONExporter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ReducedThesaurusMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ThesaurusMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Filters\Loaders\JSON\CustomFiltersLoader;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\FilterJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\CustomFiltersMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Filters\Loaders\Memory\CustomFiltersAndThesaurusLoader;
use CranachDigitalArchive\Importer\Modules\Filters\Transformers\AlphabeticSorter;
use CranachDigitalArchive\Importer\Modules\Filters\Transformers\NumericalSorter;
use CranachDigitalArchive\Importer\Modules\Locations\Sources\LocationsSource;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\LocationsGeoPositionExtender;

$date = '20230315';
$inputDirectory = './input/' . $date;
$destDirectory = './docs/' . $date;
$resourcesDirectory = './resources';

/* Read .env file */
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);

try {
    $dotenv->load();
} catch (\Throwable $e) {
    echo "Missing .env file!\nSee README.md for more.\n\n";
    exit();
}

$imagesAPIKey = $_ENV['IMAGES_API_KEY'];
$cacheDir = $_ENV['CACHE_DIR'] ?? './.cache';

/* Inputfiles */
$thesaurusInputFilepath = $inputDirectory . '/CDA_Thesaurus_' . $date . '.xml';
$paintingsRestorationInputFilepaths = [
    $inputDirectory . '/CDA_RestDokumente_P1_' . $date . '.xml',
    $inputDirectory . '/CDA_RestDokumente_P2_' . $date . '.xml',
    $inputDirectory . '/CDA_RestDokumente_P3_' . $date . '.xml',
];
$paintingsInputFilepaths = [
    $inputDirectory . '/CDA_Datenuebersicht_P1_' . $date . '.xml',
    $inputDirectory . '/CDA_Datenuebersicht_P2_' . $date . '.xml',
    $inputDirectory . '/CDA_Datenuebersicht_P3_' . $date . '.xml',
];
$graphicsRestorationInputFilepaths = [
    $inputDirectory . '/CDA-GR_RestDokumente_' . $date . '.xml',
];
$graphicsInputFilepath = $inputDirectory . '/CDA-GR_Datenuebersicht_' . $date . '.xml';
$literatureInputFilepaths = [
    $inputDirectory . '/CDA_Literaturverweise_P1_' . $date . '.xml',
    $inputDirectory . '/CDA_Literaturverweise_P2_' . $date . '.xml',
];
$archivalsInputFilepath = $inputDirectory . '/CDA-A_Datenuebersicht_' . $date . '.xml';

$customFilterDefinitionsFilepath = $resourcesDirectory . '/custom_filters.json';
$locationsFilepath = $resourcesDirectory . '/locations.json';


/* Outputfiles */
$thesaurusOutputFilepath = $destDirectory . '/cda-thesaurus-v2.json';
$paintingsOutputFilepath = $destDirectory . '/cda-paintings-v2.json';
$paintingsElasticsearchOutputFilepath = $destDirectory . '/elasticsearch/cda-paintings-v2.bulk';
$graphicsOutputFilepath = $destDirectory . '/cda-graphics-v2.json';
$graphicsElasticsearchOutputFilepath = $destDirectory . '/elasticsearch/cda-graphics-v2.bulk';
$literatureReferenceOutputFilepath = $destDirectory . '/cda-literaturereferences-v2.json';
$literatureReferenceElasticsearchOutputFilepath = $destDirectory . '/elasticsearch/cda-literaturereferences-v2.bulk';
$archivalsOutputFilepath = $destDirectory . '/cda-archivals-v2.json';
$archivalsElasticsearchOutputFilepath = $destDirectory . '/elasticsearch/cda-archivals-v2.bulk';
$filtersOutputFilepath = $destDirectory . '/cda-filters.json';


/* Parameters */
$longOpts = [
    'keep-soft-deleted-artefacts',      /* optional */

    'refresh-remote-images-cache::',    /* optional value; default is 'all' */
    'refresh-remote-documents-cache::', /* optional value; default is 'all' */
    'refresh-all-remote-caches',        /* optional */
];

$opts = getopt('', $longOpts);

$keepSoftDeletedArterfacts = false;

$supportedCachesKeys = ['paintings', 'graphics', 'archivals'];
$remoteImagesCachesToRefresh = array_fill_keys($supportedCachesKeys, false);
$remoteDocumentsCachesToRefresh = array_fill_keys($supportedCachesKeys, false);

foreach ($opts as $opt => $value) {
    switch ($opt) {
        case 'keep-soft-deleted-artefacts':
            $keepSoftDeletedArterfacts = true;
            break;

        case 'refresh-remote-images-cache':
        case 'refresh-remote-documents-cache':
            $cachesToRefresh = $value === false ? ['all'] : explode(',', $value);
            $refreshAllCaches = in_array('all', $cachesToRefresh, true);

            $remoteCachesToRefresh = array_reduce(
                $supportedCachesKeys,
                function ($arr, $key) use ($refreshAllCaches, $cachesToRefresh) {
                    $arr[$key] = $refreshAllCaches || in_array($key, $cachesToRefresh, true);
                    return $arr;
                },
                [],
            );

            if ($opt === 'refresh-remote-images-cache') {
                $remoteImagesCachesToRefresh = $remoteCachesToRefresh;
            } elseif ($opt === 'refresh-remote-documents-cache') {
                $remoteDocumentsCachesToRefresh = $remoteCachesToRefresh;
            }
            break;

        case 'refresh-all-remote-caches':
            $remoteImagesCachesToRefresh = array_fill_keys($supportedCachesKeys, true);
            $remoteDocumentsCachesToRefresh = array_fill_keys($supportedCachesKeys, true);
            break;

        default:
    }
}

/* Locations */
$locationsSource = LocationsSource::withSourceAt($locationsFilepath);


/* MetaReferences -> Thesaurus-Links */
$metaReferenceCollector = MetaReferenceCollector::new();


/* Thesaurus */
$thesaurusMemoryDestination = ThesaurusMemoryExporter::new(); /* needed later for graphics and paintings */
$thesaurusMemoryLoader = ThesaurusMemoryLoader::withMemory($thesaurusMemoryDestination);

ThesaurusXMLLoader::withSourceAt($thesaurusInputFilepath)->pipe(
    ThesaurusJSONExporter::withDestinationAt($thesaurusOutputFilepath),
    $thesaurusMemoryDestination,
)->run(); /* and we have to run it directly */


/* Filters */
$customFiltersLoader = CustomFiltersLoader::withSourceAt($customFilterDefinitionsFilepath);
$customFiltersMemoryDestination = CustomFiltersMemoryExporter::new(); /* needed later for graphics and paintings */

$customFiltersLoader->pipe(
    $customFiltersMemoryDestination,
)->run();


/* PaintingsRestorations */
$paintingsRestorationMemoryDestination = RestorationsMemoryExporter::new();
$paintingsRestorationsIdAdder = RestorationsExtenderWithIds::new($customFiltersMemoryDestination);

RestorationsLoader::withSourcesAt($paintingsRestorationInputFilepaths)->pipe(
    $paintingsRestorationsIdAdder->pipe(
        $paintingsRestorationMemoryDestination,
    ),
)->run(); /* and we have to run it directly */


/* Paintings - Infos */
$paintingsPreLoader = PaintingsPreLoader::withSourcesAt($paintingsInputFilepaths);
$paintingsReferencesCollector = PaintingsReferencesCollector::new();
$paintingsPreLoader->pipe($paintingsReferencesCollector);
$paintingsPreLoader->run();


/* Paintings */
$paintingsRemoteDocumentExistenceChecker = RemoteDocumentExistenceChecker::withCacheAt(
    'remotePaintingsDocumentExistenceChecker',
    $cacheDir,
    $imagesAPIKey,
    $remoteDocumentsCachesToRefresh['paintings'],
);
$paintingsRemoteImageExistenceChecker = RemoteImageExistenceChecker::withCacheAt(
    'remotePaintingsImageExistenceChecker',
    $cacheDir,
    $imagesAPIKey,
    $remoteImagesCachesToRefresh['paintings']
);
$paintingsRestorationExtender = PaintingsExtenderWithRestorations::new($paintingsRestorationMemoryDestination);
$paintingsIdAdder = PaintingsExtenderWithIds::new($customFiltersMemoryDestination);
$paintingsMapToSearchablePainting = MapToSearchablePainting::new();
$paintingsBasicFilterValues = PaintingsExtenderWithBasicFilterValues::new($customFiltersMemoryDestination);
$paintingsInvolvedPersonsFullnames = PaintingsExtenderWithInvolvedPersonsFullnames::new();
$paintingsSortingInfo = PaintingsExtenderWithSortingInfo::new();
$paintingsThesaurusExtender = PaintingsExtenderWithThesaurus::new($thesaurusMemoryDestination);
$paintingsMetadataFiller = PaintingsMetadataFiller::new();
$paintingsReferencesExtender = PaintingsExtenderWithReferences::new($paintingsReferencesCollector);
$paintingsDestination = PaintingsJSONLangExporter::withDestinationAt($paintingsOutputFilepath);
$paintingsElasticsearchBulkDestination = PaintingsElasticsearchLangExporter::withDestinationAt(
    $paintingsElasticsearchOutputFilepath
);
$paintingsLocationExtender = LocationsGeoPositionExtender::new($locationsSource);

$paintingsLoader = PaintingsLoader::withSourcesAt($paintingsInputFilepaths);

$inbetweenNode = $paintingsLoader;

if (!$keepSoftDeletedArterfacts) {
    /* We skip the soft deleted artefact */
    $gate = SkipSoftDeletedArtefactGate::new('Paintings');

    $inbetweenNode->pipe($gate);
    $inbetweenNode = $gate;
}

$inbetweenNode->pipe(
    $paintingsReferencesExtender->pipe(
        $paintingsRemoteDocumentExistenceChecker->pipe(
            $paintingsRemoteImageExistenceChecker->pipe(
                $paintingsRestorationExtender->pipe(
                    $paintingsIdAdder->pipe(
                        $paintingsMetadataFiller->pipe(
                            $paintingsSortingInfo->pipe(
                                $paintingsLocationExtender->pipe(
                                    $paintingsDestination,
                                    $metaReferenceCollector,
                                    $paintingsMapToSearchablePainting->pipe(
                                        $paintingsThesaurusExtender->pipe(
                                            $paintingsBasicFilterValues->pipe(
                                                $paintingsInvolvedPersonsFullnames->pipe(
                                                    $paintingsElasticsearchBulkDestination,
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);


/* GraphicRestorations */
$graphicsRestorationMemoryDestination = RestorationsMemoryExporter::new();

RestorationsLoader::withSourcesAt($graphicsRestorationInputFilepaths)->pipe(
    $graphicsRestorationMemoryDestination,
)->run(); /* and we have to run it directly */

/* Graphics - Infos */
$graphicsPreLoader = GraphicsPreLoader::withSourceAt($graphicsInputFilepath);
$graphicsLocationsCollector = GraphicsLocationsCollector::new();
$graphicsRepositoriesCollector = GraphicsRepositoriesCollector::new();
$graphicsPreLoader->pipe($graphicsLocationsCollector);
$graphicsPreLoader->pipe($graphicsRepositoriesCollector);
$graphicsPreLoader->run();


/* Graphics */
$graphicsRemoteDocumentExistenceChecker = RemoteDocumentExistenceChecker::withCacheAt(
    'remoteGraphicsDocumentExistenceChecker',
    $cacheDir,
    $imagesAPIKey,
    $remoteDocumentsCachesToRefresh['graphics']
);
$graphicsRemoteImageExistenceChecker = RemoteImageExistenceChecker::withCacheAt(
    'remoteGraphicsImageExistenceChecker',
    $cacheDir,
    $imagesAPIKey,
    $remoteImagesCachesToRefresh['graphics']
);
$graphicsConditionDeterminer = ConditionDeterminer::new();
$graphicsRestorationExtender = GraphicsExtenderWithRestorations::new($graphicsRestorationMemoryDestination);
$graphicsMapToSearchableGraphic = MapToSearchableGraphic::new();
$graphicsIdAdder = GraphicsExtenderWithIds::new($customFiltersMemoryDestination);
$graphicsBasicFilterValues = GraphicsExtenderWithBasicFilterValues::new($customFiltersMemoryDestination);
$graphicsInvolvedPersonsFullnames = GraphicsExtenderWithInvolvedPersonsFullnames::new();
$graphicsSortingInfo = GraphicsExtenderWithSortingInfo::new();
$graphicsThesaurusExtender = GraphicsExtenderWithThesaurus::new($thesaurusMemoryDestination);
$graphicsMetadataFiller = GraphicsMetadataFiller::new();
$graphicsLocationsExtender = GraphicsExtenderWithLocations::new($graphicsLocationsCollector, true);
$graphicsRepositoriesExtender = GraphicsExtenderWithRepositories::new($graphicsRepositoriesCollector, true);
$graphicsDestination = GraphicsJSONLangExistenceTypeExporter::withDestinationAt($graphicsOutputFilepath);
$graphicsElasticsearchBulkDestination = GraphicsElasticsearchLangExporter::withDestinationAt(
    $graphicsElasticsearchOutputFilepath
);
$graphicsLocationsGeoPositionExtender = LocationsGeoPositionExtender::new($locationsSource);

$graphicsLoader = GraphicsLoader::withSourceAt($graphicsInputFilepath);

$inbetweenNode = $graphicsLoader;
if (!$keepSoftDeletedArterfacts) {
    /* We skip the soft deleted artefact */
    $gate = SkipSoftDeletedArtefactGate::new('Graphics');

    $inbetweenNode ->pipe($gate);
    $inbetweenNode  = $gate;
}

$inbetweenNode->pipe(
    $graphicsRemoteDocumentExistenceChecker->pipe(
        $graphicsRemoteImageExistenceChecker->pipe(
            $graphicsIdAdder->pipe(
                $graphicsConditionDeterminer->pipe(
                    $graphicsRestorationExtender->pipe(
                        $graphicsMetadataFiller->pipe(
                            $graphicsLocationsExtender->pipe(
                                $graphicsSortingInfo->pipe(
                                    $graphicsLocationsGeoPositionExtender->pipe(
                                        $graphicsDestination,
                                        $metaReferenceCollector,
                                        $graphicsMapToSearchableGraphic->pipe(
                                            $graphicsRepositoriesExtender->pipe(
                                                $graphicsThesaurusExtender->pipe(
                                                    $graphicsBasicFilterValues->pipe(
                                                        $graphicsInvolvedPersonsFullnames->pipe(
                                                            $graphicsElasticsearchBulkDestination,
                                                        ),
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);


/* LiteratureReferences */
$literatureReferencesMetadataFiller = LiteratureReferencesMetadataFiller::new();
$literatureReferencesExtenderWithAggregatedData = ExtenderWithAggregatedData::new();
$literatureReferencesLoader = LiteratureReferencesLoader::withSourcesAt($literatureInputFilepaths)->pipe(
    $literatureReferencesMetadataFiller->pipe(
        $literatureReferencesExtenderWithAggregatedData->pipe(
            LiteratureReferencesJSONLangExporter::withDestinationAt($literatureReferenceOutputFilepath),
            LiteratureReferencesElasticsearchLangExporter::withDestinationAt(
                $literatureReferenceElasticsearchOutputFilepath
            ),
        ),
    ),
);


/* Archivals */
$archivalsDestination = ArchivalsJSONLangExporter::withDestinationAt($archivalsOutputFilepath);
$archivalsMetadataFiller = ArchivalsMetadataFiller::new();
$archivalsRemoteDocumentExistenceChecker = RemoteDocumentExistenceChecker::withCacheAt(
    'remoteArchivalsDocumentExistenceChecker',
    $cacheDir,
    $imagesAPIKey,
    $remoteDocumentsCachesToRefresh['archivals']
);

$archivalsRemoteImageExistenceChecker = RemoteImageExistenceChecker::withCacheAt(
    'remoteArchivalsImageExistenceChecker',
    $cacheDir,
    $imagesAPIKey,
    $remoteImagesCachesToRefresh['archivals']
);

$archivalsMapToSearchableArchival = MapToSearchableArchival::new();
$archivalsExtenderWithRepositoryId = ExtenderWithRepositoryId::new();

$archivalsElasticsearchBulkDestination = ArchivalsElasticsearchLangExporter::withDestinationAt(
    $archivalsElasticsearchOutputFilepath
);

$archivalsLoader = ArchivalsLoader::withSourceAt($archivalsInputFilepath)->pipe(
    $archivalsRemoteDocumentExistenceChecker->pipe(
        $archivalsRemoteImageExistenceChecker->pipe(
            $archivalsMetadataFiller->pipe(
                $archivalsDestination,
                $archivalsMapToSearchableArchival->pipe(
                    $archivalsExtenderWithRepositoryId->pipe(
                        $archivalsElasticsearchBulkDestination
                    )
                )
            ),
        ),
    ),
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


$restrictedTermIds = array_map(
    function ($metaReference) {
        return $metaReference->getTerm();
    },
    array_values($metaReferenceCollector->getCollection()),
);

CustomFiltersAndThesaurusLoader::withMemory(
    $customFiltersMemoryDestination,
    ReducedThesaurusMemoryExporter::new($thesaurusMemoryDestination, $restrictedTermIds),
)->pipe(
    NumericalSorter::new()->pipe(
        AlphabeticSorter::new()->pipe(
            FilterJSONLangExporter::withDestinationAt($filtersOutputFilepath),
        )
    )
)->run();


$locationsSource->store();

$customFiltersMemoryDestination->cleanUp();
$thesaurusMemoryDestination->cleanUp();
$paintingsRestorationMemoryDestination->cleanUp();
$graphicsRestorationMemoryDestination->cleanUp();
$paintingsReferencesCollector->cleanUp();
$graphicsLocationsCollector->cleanUp();


$metaReferenceCollector->cleanUp();
$locationsSource->cleanUp();
