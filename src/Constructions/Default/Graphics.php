<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Caches\FileCache;
use CranachDigitalArchive\Importer\Constructions\Default\Utils\Parameters;
use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\LocationsGeoPositionExtender;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\RemoteDocumentExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\RemoteImageExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Main\Gates\SkipSoftDeletedArtefactGate;
use CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML\GraphicsPreLoader;
use CranachDigitalArchive\Importer\Modules\Graphics\Collectors\LocationsCollector;
use CranachDigitalArchive\Importer\Modules\Graphics\Collectors\RepositoriesCollector;
use CranachDigitalArchive\Importer\Modules\Graphics\Exporters\GraphicsJSONLangExistenceTypeExporter;
use CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML\GraphicsLoader;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithIds;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ConditionDeterminer;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithRestorations;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\MetadataFiller;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithLocations;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithSortingInfo;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\MapToSearchableGraphic;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithRepositories as SearchableGraphicsExtenderWithRepositories;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithThesaurus as SearchableGraphicsExtenderWithThesaurus;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithBasicFilterValues as SearchableGraphicsExtenderWithBasicFilterValues;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ExtenderWithInvolvedPersonsFullnames as SearchableGraphicsExtenderWithInvolvedPersonsFullnames;
use CranachDigitalArchive\Importer\Modules\Graphics\Exporters\GraphicsElasticsearchLangExporter as SearchableGraphicsGraphicsElasticsearchLangExporter;

final class Graphics
{
    private LocationsCollector $graphicsLocationsCollector;
    private RepositoriesCollector $graphicsRepositoriesCollector;
    private GraphicsLoader $loader;

    private function __construct(
        Paths $paths,
        Parameters $parameters,
        Base $base,
        MemoryFilters $memoryFilters,
        Thesaurus $thesaurus,
        GraphicsRestoration $graphicssRestoration,
    ) {
        $graphicsOutputFilepath = $paths->getOutputPath('cda-graphics-v2.json');
        $graphicsElasticsearchOutputFilepath = $paths->getElasticsearchOutputPath('cda-graphics-v2.bulk');

        /* Graphics - Infos */
        $graphicsPreLoader = GraphicsPreLoader::withSourcesAt($paths->getGraphicsInputFilePaths());
        $this->graphicsLocationsCollector = LocationsCollector::new();
        $this->graphicsRepositoriesCollector = RepositoriesCollector::new();
        $graphicsPreLoader
            ->pipeline($this->graphicsLocationsCollector)
            ->pipeline($this->graphicsRepositoriesCollector)
            ->run();


        /* Graphics */
        $graphicsRemoteDocumentExistenceChecker = RemoteDocumentExistenceChecker::new(
            $parameters->getEnvironmentVariables()->getImagesAPIKey(),
        )->withCache(FileCache::new(
            'remoteGraphicsDocumentExistenceChecker',
            $paths->getCacheDirectoryPath(),
            $parameters->getRemoteDocumentsCachesToRefresh()['graphics'],
        ));
        $graphicsRemoteImageExistenceChecker = RemoteImageExistenceChecker::new(
            $parameters->getEnvironmentVariables()->getImagesAPIKey(),
        )->withCache(FileCache::new(
            'remoteGraphicsImageExistenceChecker',
            $paths->getCacheDirectoryPath(),
            $parameters->getRemoteImagesCachesToRefresh()['graphics'],
        ));

        $this->loader = GraphicsLoader::withSourcesAt($paths->getGraphicsInputFilePaths());
        $this->loader->pipeline(
            (!$parameters->getKeepSoftDeletedAretefacts()) ? SkipSoftDeletedArtefactGate::new('Graphics') : null,
            $graphicsRemoteDocumentExistenceChecker,
            $graphicsRemoteImageExistenceChecker,
            ExtenderWithIds::new($memoryFilters->getMemoryExporter()),
            ConditionDeterminer::new(),
            ExtenderWithRestorations::new($graphicssRestoration->getMemoryExporter()),
            MetadataFiller::new(),
            ExtenderWithLocations::new($this->graphicsLocationsCollector, true),
            ExtenderWithSortingInfo::new(),
            LocationsGeoPositionExtender::new($base->getLocationsSource())
                /* Exporting the graphics as JSON */
                ->pipeline(GraphicsJSONLangExistenceTypeExporter::withDestinationAt($graphicsOutputFilepath))
                /* Collecting all meta references / keywords found in graphics */
                ->pipeline($base->getMetaReferenceCollector())
                /* Map Graphics to SearchableGraphics */
                ->pipeline(
                    MapToSearchableGraphic::new()->pipeline(
                        SearchableGraphicsExtenderWithRepositories::new($this->graphicsRepositoriesCollector, true),
                        SearchableGraphicsExtenderWithThesaurus::new($thesaurus->getMemoryExporter()),
                        SearchableGraphicsExtenderWithBasicFilterValues::new($memoryFilters->getMemoryExporter()),
                        SearchableGraphicsExtenderWithInvolvedPersonsFullnames::new(),
                        /* Exporting the graphics for Elasticsearch bulk import */
                        SearchableGraphicsGraphicsElasticsearchLangExporter::withDestinationAt($graphicsElasticsearchOutputFilepath),
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
        GraphicsRestoration $graphicssRestoration,
    ): self {
        return new self(
            $paths,
            $parameters,
            $base,
            $memoryFilters,
            $thesaurus,
            $graphicssRestoration,
        );
    }

    public function run(): self
    {
        $this->loader->run();
        return $this;
    }

    public function cleanUp(): void
    {
        $this->graphicsLocationsCollector->cleanUp();
        $this->graphicsRepositoriesCollector->cleanUp();
    }
}
