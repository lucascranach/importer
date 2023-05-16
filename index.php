<?php

namespace CranachDigitalArchive\Importer;

ini_set('memory_limit', '2048M');
echo "MemoryLimit: " . ini_get('memory_limit') . "\n\n";

require_once __DIR__ . '/vendor/autoload.php';

use CranachDigitalArchive\Importer\Caches\FileCache;
use CranachDigitalArchive\Importer\InputExportsOverview;
use CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML\GraphicsLoader;
use CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML\GraphicsPreLoader;
use CranachDigitalArchive\Importer\Modules\Graphics\Collectors\LocationsCollector as GraphicsLocationsCollector;
use CranachDigitalArchive\Importer\Modules\Graphics\Collectors\RepositoriesCollector as GraphicsRepositoriesCollector;
use CranachDigitalArchive\Importer\Modules\Graphics\Exporters\GraphicsJSONLangExistenceTypeExporter;
use CranachDigitalArchive\Importer\Modules\Graphics\Exporters\GraphicsElasticsearchLangExporter as SearchableGraphicsElasticsearchLangExporter;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ConditionDeterminer;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\MapToSearchableGraphic;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithThesaurus as SearchableGraphicsExtenderWithThesaurus;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithBasicFilterValues as SearchableGraphicsExtenderWithBasicFilterValues;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithInvolvedPersonsFullnames as SearchableGraphicsExtenderWithInvolvedPersonsFullnames;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithSortingInfo as GraphicsExtenderWithSortingInfo;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithIds as GraphicsExtenderWithIds;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithRestorations as GraphicsExtenderWithRestorations;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithLocations as GraphicsExtenderWithLocations;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithRepositories as SearchableGraphicsExtenderWithRepositories;
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
use CranachDigitalArchive\Importer\Modules\Paintings\Exporters\PaintingsElasticsearchLangExporter as SearchablePaintingsElasticsearchLangExporter;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\MapToSearchablePainting;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithThesaurus as SearchablePaintingsExtenderWithThesaurus;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithBasicFilterValues as SearchablePaintingsExtenderWithBasicFilterValues;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithInvolvedPersonsFullnames as SearchablePaintingsExtenderWithInvolvedPersonsFullnames;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithSortingInfo as PaintingsExtenderWithSortingInfo;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithIds as PaintingsExtenderWithIds;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithRestorations as PaintingsExtenderWithRestorations;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithReferences as PaintingsExtenderWithReferences;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\MetadataFiller as PaintingsMetadataFiller;
use CranachDigitalArchive\Importer\Modules\Archivals\Loaders\XML\ArchivalsLoader;
use CranachDigitalArchive\Importer\Modules\Archivals\Exporters\ArchivalsJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Archivals\Exporters\ArchivalsElasticsearchLangExporter as SearchableArchivalsElasticsearchLangExporter;
use CranachDigitalArchive\Importer\Modules\Archivals\Transformers\MetadataFiller as ArchivalsMetadataFiller;
use CranachDigitalArchive\Importer\Modules\Archivals\Transformers\MapToSearchableArchival;
use CranachDigitalArchive\Importer\Modules\Archivals\Transformers\ExtenderWithRepositoryId as SearchableArchivalsExtenderWithRepositoryId;
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

$scriptDirectory = __DIR__;

$inputBaseDirectory = $scriptDirectory . '/input/';

$inputExportsOverview = InputExportsOverview::new($inputBaseDirectory);


/* Parameters */
$longOpts = [
    'keep-soft-deleted-artefacts',      /* optional */

    'refresh-remote-images-cache::',    /* optional value; default is 'all' */
    'refresh-remote-documents-cache::', /* optional value; default is 'all' */
    'refresh-all-remote-caches',        /* optional */

    'use-export:'                       /* required value */,
];

$opts = getopt('', $longOpts);

$keepSoftDeletedArterfacts = false;

$supportedCachesKeys = ['paintings', 'graphics', 'archivals'];
$remoteImagesCachesToRefresh = array_fill_keys($supportedCachesKeys, false);
$remoteDocumentsCachesToRefresh = array_fill_keys($supportedCachesKeys, false);

$selectedDate = null;

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

        case 'use-export':
            $foundDirectoryEntry = $inputExportsOverview->getDirectoryEntryWithName($value);

            if (is_null($foundDirectoryEntry)) {
                exit('Unknown export with name \'' . $value . '\'' . "\n\n");
            }

            $selectedDate = $value;
            break;

        default:
    }
}


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

if (is_null($selectedDate)) {
    $latestInputExportEntry = $inputExportsOverview->getLatestDirectoryEntry();

    if (!is_null($latestInputExportEntry)) {
        $selectedDate = $latestInputExportEntry->getFilename();
    } else {
        exit('No possible export found in \'' . $inputExportsOverview->getSearchPath() . '\'!');
    }
}

echo 'Selected Export : ' . $selectedDate . "\n\n\n";

$inputDirectory = $inputBaseDirectory . $selectedDate;
$destDirectory = $scriptDirectory .'/docs/' . $selectedDate;
$resourcesDirectory = $scriptDirectory . '/resources';

/* Inputfiles */
$thesaurusInputFilepath = $inputDirectory . '/CDA_Thesaurus_' . $selectedDate . '.xml';
$paintingsRestorationInputFilepaths = [
    $inputDirectory . '/CDA_RestDokumente_P1_' . $selectedDate . '.xml',
    $inputDirectory . '/CDA_RestDokumente_P2_' . $selectedDate . '.xml',
    $inputDirectory . '/CDA_RestDokumente_P3_' . $selectedDate . '.xml',
];
$paintingsInputFilepaths = [
    $inputDirectory . '/CDA_Datenuebersicht_P1_' . $selectedDate . '.xml',
    $inputDirectory . '/CDA_Datenuebersicht_P2_' . $selectedDate . '.xml',
    $inputDirectory . '/CDA_Datenuebersicht_P3_' . $selectedDate . '.xml',
];
$graphicsRestorationInputFilepaths = [
    $inputDirectory . '/CDA-GR_RestDokumente_' . $selectedDate . '.xml',
];
$graphicsInputFilepath = $inputDirectory . '/CDA-GR_Datenuebersicht_' . $selectedDate . '.xml';
$literatureInputFilepaths = [
    $inputDirectory . '/CDA_Literaturverweise_P1_' . $selectedDate . '.xml',
    $inputDirectory . '/CDA_Literaturverweise_P2_' . $selectedDate . '.xml',
];
$archivalsInputFilepath = $inputDirectory . '/CDA-A_Datenuebersicht_' . $selectedDate . '.xml';

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



/* Locations */
$locationsSource = LocationsSource::withSourceAt($locationsFilepath);


/* MetaReferences -> Thesaurus-Links */
$metaReferenceCollector = MetaReferenceCollector::new();


/* Thesaurus */
$thesaurusMemoryDestination = ThesaurusMemoryExporter::new(); /* needed later for graphics and paintings */
$thesaurusMemoryLoader = ThesaurusMemoryLoader::withMemory($thesaurusMemoryDestination);

ThesaurusXMLLoader::withSourceAt($thesaurusInputFilepath)
    ->pipeline(ThesaurusJSONExporter::withDestinationAt($thesaurusOutputFilepath))
    ->pipeline($thesaurusMemoryDestination)
    ->run(); /* and we have to run it directly */


/* Filters */
$customFiltersLoader = CustomFiltersLoader::withSourceAt($customFilterDefinitionsFilepath);
$customFiltersMemoryDestination = CustomFiltersMemoryExporter::new(); /* needed later for graphics and paintings */

$customFiltersLoader
    ->pipeline($customFiltersMemoryDestination)
    ->run();


/* PaintingsRestorations */
$paintingsRestorationMemoryDestination = RestorationsMemoryExporter::new();
$paintingsRestorationsIdAdder = RestorationsExtenderWithIds::new($customFiltersMemoryDestination);

RestorationsLoader::withSourcesAt($paintingsRestorationInputFilepaths)
    ->pipeline(
        $paintingsRestorationsIdAdder,
        $paintingsRestorationMemoryDestination
    )
    ->run(); /* and we have to run it directly */


/* Paintings - Infos */
$paintingsPreLoader = PaintingsPreLoader::withSourcesAt($paintingsInputFilepaths);
$paintingsReferencesCollector = PaintingsReferencesCollector::new();
$paintingsPreLoader
    ->pipeline($paintingsReferencesCollector)
    ->run();


/* Paintings */
$paintingsRemoteDocumentExistenceChecker = RemoteDocumentExistenceChecker::new(
    $imagesAPIKey,
)->withCache(FileCache::new(
    'remotePaintingsDocumentExistenceChecker',
    $cacheDir,
    $remoteDocumentsCachesToRefresh['paintings']
));
$paintingsRemoteImageExistenceChecker = RemoteImageExistenceChecker::new(
    $imagesAPIKey,
)->withCache(FileCache::new(
    'remotePaintingsImageExistenceChecker',
    $cacheDir,
    $remoteImagesCachesToRefresh['paintings']
));

$paintingsLoader = PaintingsLoader::withSourcesAt($paintingsInputFilepaths)->pipeline(
    (!$keepSoftDeletedArterfacts) ? SkipSoftDeletedArtefactGate::new('Paintings'): null,
    PaintingsExtenderWithReferences::new($paintingsReferencesCollector),
    $paintingsRemoteDocumentExistenceChecker,
    $paintingsRemoteImageExistenceChecker,
    PaintingsExtenderWithRestorations::new($paintingsRestorationMemoryDestination),
    PaintingsExtenderWithIds::new($customFiltersMemoryDestination),
    PaintingsMetadataFiller::new(),
    PaintingsExtenderWithSortingInfo::new(),
    LocationsGeoPositionExtender::new($locationsSource)
        /* Exporting the paintings as JSON */
        ->pipeline(PaintingsJSONLangExporter::withDestinationAt($paintingsOutputFilepath))
        /* Collecting all meta references / keywords found in paintings */
        ->pipeline($metaReferenceCollector)
        /* Map Paintings to SearchablePaintings */
        ->pipeline(
            MapToSearchablePainting::new()->pipeline(
                SearchablePaintingsExtenderWithThesaurus::new($thesaurusMemoryDestination),
                SearchablePaintingsExtenderWithBasicFilterValues::new($customFiltersMemoryDestination),
                SearchablePaintingsExtenderWithInvolvedPersonsFullnames::new(),
                /* Exporting the paintings for Elasticsearch bulk import */
                SearchablePaintingsElasticsearchLangExporter::withDestinationAt($paintingsElasticsearchOutputFilepath),
            ),
        ),
);

/* GraphicRestorations */
$graphicsRestorationMemoryDestination = RestorationsMemoryExporter::new();

RestorationsLoader::withSourcesAt($graphicsRestorationInputFilepaths)
    ->pipeline($graphicsRestorationMemoryDestination)
    ->run(); /* and we have to run it directly */

/* Graphics - Infos */
$graphicsPreLoader = GraphicsPreLoader::withSourceAt($graphicsInputFilepath);
$graphicsLocationsCollector = GraphicsLocationsCollector::new();
$graphicsRepositoriesCollector = GraphicsRepositoriesCollector::new();
$graphicsPreLoader
    ->pipeline($graphicsLocationsCollector)
    ->pipeline($graphicsRepositoriesCollector)
    ->run();


/* Graphics */
$graphicsRemoteDocumentExistenceChecker = RemoteDocumentExistenceChecker::new(
    $imagesAPIKey,
)->withCache(FileCache::new(
    'remoteGraphicsDocumentExistenceChecker',
    $cacheDir,
    $remoteDocumentsCachesToRefresh['graphics'],
));
$graphicsRemoteImageExistenceChecker = RemoteImageExistenceChecker::new(
    $imagesAPIKey,
)->withCache(FileCache::new(
    'remoteGraphicsImageExistenceChecker',
    $cacheDir,
    $remoteImagesCachesToRefresh['graphics']
));

$graphicsLoader = GraphicsLoader::withSourceAt($graphicsInputFilepath)->pipeline(
    (!$keepSoftDeletedArterfacts) ? SkipSoftDeletedArtefactGate::new('Graphics') : null,
    $graphicsRemoteDocumentExistenceChecker,
    $graphicsRemoteImageExistenceChecker,
    GraphicsExtenderWithIds::new($customFiltersMemoryDestination),
    ConditionDeterminer::new(),
    GraphicsExtenderWithRestorations::new($graphicsRestorationMemoryDestination),
    GraphicsMetadataFiller::new(),
    GraphicsExtenderWithLocations::new($graphicsLocationsCollector, true),
    GraphicsExtenderWithSortingInfo::new(),
    LocationsGeoPositionExtender::new($locationsSource)
        /* Exporting the graphics as JSON */
        ->pipeline(GraphicsJSONLangExistenceTypeExporter::withDestinationAt($graphicsOutputFilepath))
        /* Collecting all meta references / keywords found in graphics */
        ->pipeline($metaReferenceCollector)
        /* Map Graphics to SearchableGraphics */
        ->pipeline(
            MapToSearchableGraphic::new()->pipeline(
                SearchableGraphicsExtenderWithRepositories::new($graphicsRepositoriesCollector, true),
                SearchableGraphicsExtenderWithThesaurus::new($thesaurusMemoryDestination),
                SearchableGraphicsExtenderWithBasicFilterValues::new($customFiltersMemoryDestination),
                SearchableGraphicsExtenderWithInvolvedPersonsFullnames::new(),
                /* Exporting the graphics for Elasticsearch bulk import */
                SearchableGraphicsElasticsearchLangExporter::withDestinationAt($graphicsElasticsearchOutputFilepath),
            ),
        ),
);


/* LiteratureReferences */
$literatureReferencesLoader = LiteratureReferencesLoader::withSourcesAt($literatureInputFilepaths)->pipeline(
    LiteratureReferencesMetadataFiller::new(),
    ExtenderWithAggregatedData::new()
        /* Exporting the literatureReferences as JSON */
        ->pipeline(
            LiteratureReferencesJSONLangExporter::withDestinationAt($literatureReferenceOutputFilepath),
        )
        /* Exporting the literatureReferences for Elasticsearch bulk import */
        ->pipeline(
            LiteratureReferencesElasticsearchLangExporter::withDestinationAt($literatureReferenceElasticsearchOutputFilepath),
        )
);


/* Archivals */
$archivalsRemoteDocumentExistenceChecker = RemoteDocumentExistenceChecker::new(
    $imagesAPIKey,
)->withCache(FileCache::new(
    'remoteArchivalsDocumentExistenceChecker',
    $cacheDir,
    $remoteDocumentsCachesToRefresh['archivals']
));
$archivalsRemoteImageExistenceChecker = RemoteImageExistenceChecker::new(
    $imagesAPIKey,
)->withCache(FileCache::new(
    'remoteArchivalsImageExistenceChecker',
    $cacheDir,
    $remoteImagesCachesToRefresh['archivals']
));

$archivalsLoader = ArchivalsLoader::withSourceAt($archivalsInputFilepath)->pipeline(
    $archivalsRemoteDocumentExistenceChecker,
    $archivalsRemoteImageExistenceChecker,
    ArchivalsMetadataFiller::new()
        /* Exporting the archivals as JSON */
        ->pipeline(ArchivalsJSONLangExporter::withDestinationAt($archivalsOutputFilepath))
        /* Map Archivals to SearchableArchivals */
        ->pipeline(
            MapToSearchableArchival::new()->pipeline(
                SearchableArchivalsExtenderWithRepositoryId::new(),
                /* Exporting the archivals for Elasticsearch bulk import */
                SearchableArchivalsElasticsearchLangExporter::withDestinationAt($archivalsElasticsearchOutputFilepath),
            )
        )
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
)
    ->pipeline(
        NumericalSorter::new(),
        AlphabeticSorter::new(),
        FilterJSONLangExporter::withDestinationAt($filtersOutputFilepath),
    )
    ->run();


$locationsSource->store();

$customFiltersMemoryDestination->cleanUp();
$thesaurusMemoryDestination->cleanUp();
$paintingsRestorationMemoryDestination->cleanUp();
$graphicsRestorationMemoryDestination->cleanUp();
$paintingsReferencesCollector->cleanUp();
$graphicsLocationsCollector->cleanUp();


$metaReferenceCollector->cleanUp();
$locationsSource->cleanUp();
