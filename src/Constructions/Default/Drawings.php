<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Caches\FileCache;
use CranachDigitalArchive\Importer\Constructions\Default\Utils\Parameters;
use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Modules\Main\Gates\SkipSoftDeletedArtefactGate;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\RemoteDocumentExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\RemoteImageExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Drawings\Loaders\XML\DrawingsPreLoader;
use CranachDigitalArchive\Importer\Modules\Drawings\Collectors\ReferencesCollector;
use CranachDigitalArchive\Importer\Modules\Drawings\Loaders\XML\DrawingsLoader;
use CranachDigitalArchive\Importer\Modules\Drawings\Transformers\ExtenderWithReferences;
use CranachDigitalArchive\Importer\Modules\Drawings\Transformers\ReferenceDivider;
use CranachDigitalArchive\Importer\Modules\Drawings\Transformers\ExtenderWithRestorations;
use CranachDigitalArchive\Importer\Modules\Drawings\Transformers\ExtenderWithIds;
use CranachDigitalArchive\Importer\Modules\Drawings\Transformers\MetadataFiller;
use CranachDigitalArchive\Importer\Modules\Drawings\Transformers\ExtenderWithSortingInfo;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\LocationsGeoPositionExtender;
use CranachDigitalArchive\Importer\Modules\Drawings\Exporters\DrawingsJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Drawings\Transformers\MapToSearchableDrawing;
use CranachDigitalArchive\Importer\Modules\Drawings\Transformers\ExtenderWithThesaurus as SearchableDrawingsExtenderWithThesaurus;
use CranachDigitalArchive\Importer\Modules\Drawings\Transformers\ExtenderWithBasicFilterValues as SearchableDrawingsExtenderWithBasicFilterValues;
use CranachDigitalArchive\Importer\Modules\Drawings\Transformers\ExtenderWithInvolvedPersonsFullnames as SearchableDrawingsExtenderWithInvolvedPersonsFullnames;
use CranachDigitalArchive\Importer\Modules\Drawings\Exporters\DrawingsElasticsearchLangExporter as SearchableDrawingsElasticsearchLangExporter;

final class Drawings
{
    private ReferencesCollector $drawingsReferencesCollector;
    private DrawingsLoader $loader;

    private function __construct(
        Paths $paths,
        Parameters $parameters,
        Base $base,
        MemoryFilters $memoryFilters,
        Thesaurus $thesaurus,
        //DrawingsRestoration $drawingsRestoration,
    ) {
        $drawingsOutputFilepath = $paths->getOutputPath('cda-drawings-v2.json');
        $drawingsElasticsearchOutputFilepath = $paths->getElasticsearchOutputPath('cda-drawings-v2.bulk');

        /* Drawings - Infos */
        $drawingsPreLoader = DrawingsPreLoader::withSourcesAt($paths->getDrawingsInputFilePaths());
        $this->drawingsReferencesCollector = ReferencesCollector::new();
        $drawingsPreLoader
            ->pipeline($this->drawingsReferencesCollector)
            ->run();


        /* Drawings */
        $drawingsRemoteDocumentExistenceChecker = RemoteDocumentExistenceChecker::new(
            $parameters->getEnvironmentVariables()->getImagesAPIKey(),
        )->withCache(FileCache::new(
            'remoteDrawingsDocumentExistenceChecker',
            $paths->getCacheDirectoryPath(),
            $parameters->getRemoteDocumentsCachesToRefresh()['drawings'],
        ));
        $drawingsRemoteImageExistenceChecker = RemoteImageExistenceChecker::new(
            $parameters->getEnvironmentVariables()->getImagesAPIKey(),
        )->withCache(FileCache::new(
            'remoteDrawingsImageExistenceChecker',
            $paths->getCacheDirectoryPath(),
            $parameters->getRemoteImagesCachesToRefresh()['drawings'],
        ));

        $this->loader = DrawingsLoader::withSourcesAt($paths->getDrawingsInputFilePaths());
        $this->loader->pipeline(
            (!$parameters->getKeepSoftDeletedAretefacts()) ? SkipSoftDeletedArtefactGate::new('Drawings'): null,
            ExtenderWithReferences::new($this->drawingsReferencesCollector),
            ReferenceDivider::new($this->drawingsReferencesCollector),
            $drawingsRemoteDocumentExistenceChecker,
            $drawingsRemoteImageExistenceChecker,
            //ExtenderWithRestorations::new($drawingsRestoration->getMemoryExporter()),
            ExtenderWithIds::new($memoryFilters->getArtefactMemoryExporter()),
            MetadataFiller::new(),
            ExtenderWithSortingInfo::new(),
            LocationsGeoPositionExtender::new($base->getLocationsSource())
                /* Exporting the drawings as JSON */
                ->pipeline(DrawingsJSONLangExporter::withDestinationAt($drawingsOutputFilepath))
                /* Collecting all meta references / keywords found in drawings */
                ->pipeline($base->getMetaReferenceCollector())
                /* Map Drawings to SearchableDrawings */
                ->pipeline(
                    MapToSearchableDrawing::new()->pipeline(
                        SearchableDrawingsExtenderWithThesaurus::new($thesaurus->getMemoryExporter()),
                        SearchableDrawingsExtenderWithBasicFilterValues::new($memoryFilters->getArtefactMemoryExporter()),
                        SearchableDrawingsExtenderWithInvolvedPersonsFullnames::new(),
                        /* Exporting the drawings for Elasticsearch bulk import */
                        SearchableDrawingsElasticsearchLangExporter::withDestinationAt($drawingsElasticsearchOutputFilepath),
                    ),
                ),
        );
    }

    public static function new(
        Paths $paths,
        Parameters $parameters,
        Base $base,
        MemoryFilters $memoryFilters,
        Thesaurus $thesaurus,
        //DrawingsRestoration $drawingsRestoration,
    ): self {
        return new self(
            $paths,
            $parameters,
            $base,
            $memoryFilters,
            $thesaurus,
            //$drawingsRestoration,
        );
    }

    public function run(): self
    {
        $this->loader->run();
        return $this;
    }

    public function cleanUp(): void
    {
        $this->drawingsReferencesCollector->cleanUp();
    }
}
