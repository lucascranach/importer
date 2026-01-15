<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Caches\FileCache;
use CranachDigitalArchive\Importer\Constructions\Default\Utils\Parameters;
use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\ExcludeByInventoryNumberPrefix;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\RemoteDocumentExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Main\Transformers\RemoteImageExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Archivals\Loaders\XML\ArchivalsLoader;
use CranachDigitalArchive\Importer\Modules\Archivals\Exporters\ArchivalsJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Archivals\Transformers\MapToSearchableArchival;
use CranachDigitalArchive\Importer\Modules\Archivals\Transformers\MetadataFiller;
use CranachDigitalArchive\Importer\Modules\Archivals\Transformers\ExtenderWithRepositoryId as SearchableArchivalsExtenderWithRepositoryId;
use CranachDigitalArchive\Importer\Modules\Archivals\Transformers\ExtenderWithSortingInfo;
use CranachDigitalArchive\Importer\Modules\Archivals\Exporters\ArchivalsElasticsearchLangExporter as SearchableArchivalsElasticsearchLangExporter;

final class Archivals
{
    private ArchivalsLoader $loader;

    private function __construct(
        Paths $paths,
        Parameters $parameters,
    ) {
        $archivalsOutputFilepath = $paths->getOutputPath('cda-archivals-v2.json');
        $archivalsElasticsearchOutputFilepath = $paths->getElasticsearchOutputPath('cda-archivals-v2.bulk');

        /* Archivals */
        $archivalsRemoteDocumentExistenceChecker = RemoteDocumentExistenceChecker::new(
            $parameters->getEnvironmentVariables()->getImagesAPIKey(),
        )->withCache(FileCache::new(
            'remoteArchivalsDocumentExistenceChecker',
            $paths->getCacheDirectoryPath(),
            $parameters->getRemoteDocumentsCachesToRefresh()['archivals']
        ));
        $archivalsRemoteImageExistenceChecker = RemoteImageExistenceChecker::new(
            $parameters->getEnvironmentVariables()->getImagesAPIKey(),
        )->withCache(FileCache::new(
            'remoteArchivalsImageExistenceChecker',
            $paths->getCacheDirectoryPath(),
            $parameters->getRemoteDocumentsCachesToRefresh()['archivals']
        ));

        $this->loader = ArchivalsLoader::withSourcesAt($paths->getArchivalsInputFilePaths());
        $this->loader->pipeline(
            ExcludeByInventoryNumberPrefix::new($parameters->getEnvironmentVariables()->getExcludeInventoryNumberPrefix(), 'Archivals'),
            $archivalsRemoteDocumentExistenceChecker,
            $archivalsRemoteImageExistenceChecker,
            MetadataFiller::new(),
            ExtenderWithSortingInfo::new()
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

    }

    public static function new(
        Paths $paths,
        Parameters $parameters,
    ): self {
        return new self($paths, $parameters);
    }

    public function run(): self
    {
        $this->loader->run();
        return $this;
    }
}
