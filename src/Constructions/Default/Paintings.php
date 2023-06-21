<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Caches\FileCache;
use CranachDigitalArchive\Importer\Constructions\Default\Utils\Parameters;
use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Modules\Main\Gates\SkipSoftDeletedArtefactGate;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\RemoteDocumentExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\RemoteImageExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Paintings\Loaders\XML\PaintingsPreLoader;
use CranachDigitalArchive\Importer\Modules\Paintings\Collectors\ReferencesCollector;
use CranachDigitalArchive\Importer\Modules\Paintings\Loaders\XML\PaintingsLoader;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithReferences;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithRestorations;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithIds;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\MetadataFiller;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithSortingInfo;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\LocationsGeoPositionExtender;
use CranachDigitalArchive\Importer\Modules\Paintings\Exporters\PaintingsJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\MapToSearchablePainting;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithThesaurus as SearchablePaintingsExtenderWithThesaurus;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithBasicFilterValues as SearchablePaintingsExtenderWithBasicFilterValues;
use CranachDigitalArchive\Importer\Modules\Paintings\Transformers\ExtenderWithInvolvedPersonsFullnames as SearchablePaintingsExtenderWithInvolvedPersonsFullnames;
use CranachDigitalArchive\Importer\Modules\Paintings\Exporters\PaintingsElasticsearchLangExporter as SearchablePaintingsElasticsearchLangExporter;

final class Paintings
{
    private ReferencesCollector $paintingsReferencesCollector;
    private PaintingsLoader $loader;

    private function __construct(
        Paths $paths,
        Parameters $parameters,
        Base $base,
        MemoryArtefactFilters $memoryArtefactFilters,
        Thesaurus $thesaurus,
        PaintingsRestoration $paintingsRestoration,
    ) {
        $paintingsOutputFilepath = $paths->getOutputPath('cda-paintings-v2.json');
        $paintingsElasticsearchOutputFilepath = $paths->getElasticsearchOutputPath('cda-paintings-v2.bulk');

        /* Paintings - Infos */
        $paintingsPreLoader = PaintingsPreLoader::withSourcesAt($paths->getPaintingsInputFilePaths());
        $this->paintingsReferencesCollector = ReferencesCollector::new();
        $paintingsPreLoader
            ->pipeline($this->paintingsReferencesCollector)
            ->run();


        /* Paintings */
        $paintingsRemoteDocumentExistenceChecker = RemoteDocumentExistenceChecker::new(
            $parameters->getEnvironmentVariables()->getImagesAPIKey(),
        )->withCache(FileCache::new(
            'remotePaintingsDocumentExistenceChecker',
            $paths->getCacheDirectoryPath(),
            $parameters->getRemoteDocumentsCachesToRefresh()['paintings'],
        ));
        $paintingsRemoteImageExistenceChecker = RemoteImageExistenceChecker::new(
            $parameters->getEnvironmentVariables()->getImagesAPIKey(),
        )->withCache(FileCache::new(
            'remotePaintingsImageExistenceChecker',
            $paths->getCacheDirectoryPath(),
            $parameters->getRemoteImagesCachesToRefresh()['paintings'],
        ));

        $this->loader = PaintingsLoader::withSourcesAt($paths->getPaintingsInputFilePaths());
        $this->loader->pipeline(
            (!$parameters->getKeepSoftDeletedAretefacts()) ? SkipSoftDeletedArtefactGate::new('Paintings'): null,
            ExtenderWithReferences::new($this->paintingsReferencesCollector),
            $paintingsRemoteDocumentExistenceChecker,
            $paintingsRemoteImageExistenceChecker,
            ExtenderWithRestorations::new($paintingsRestoration->getMemoryExporter()),
            ExtenderWithIds::new($memoryArtefactFilters->getMemoryExporter()),
            MetadataFiller::new(),
            ExtenderWithSortingInfo::new(),
            LocationsGeoPositionExtender::new($base->getLocationsSource())
                /* Exporting the paintings as JSON */
                ->pipeline(PaintingsJSONLangExporter::withDestinationAt($paintingsOutputFilepath))
                /* Collecting all meta references / keywords found in paintings */
                ->pipeline($base->getMetaReferenceCollector())
                /* Map Paintings to SearchablePaintings */
                ->pipeline(
                    MapToSearchablePainting::new()->pipeline(
                        SearchablePaintingsExtenderWithThesaurus::new($thesaurus->getMemoryExporter()),
                        SearchablePaintingsExtenderWithBasicFilterValues::new($memoryArtefactFilters->getMemoryExporter()),
                        SearchablePaintingsExtenderWithInvolvedPersonsFullnames::new(),
                        /* Exporting the paintings for Elasticsearch bulk import */
                        SearchablePaintingsElasticsearchLangExporter::withDestinationAt($paintingsElasticsearchOutputFilepath),
                    ),
                ),
        );
    }

    public static function new(
        Paths $paths,
        Parameters $parameters,
        Base $base,
        MemoryArtefactFilters $memoryArtefactFilters,
        Thesaurus $thesaurus,
        PaintingsRestoration $paintingsRestoration,
    ): self {
        return new self(
            $paths,
            $parameters,
            $base,
            $memoryArtefactFilters,
            $thesaurus,
            $paintingsRestoration,
        );
    }

    public function run(): self
    {
        $this->loader->run();
        return $this;
    }

    public function cleanUp(): void
    {
        $this->paintingsReferencesCollector->cleanUp();
    }
}
