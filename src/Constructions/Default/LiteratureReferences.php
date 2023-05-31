<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Exporters\LiteratureReferencesElasticsearchLangExporter;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Exporters\LiteratureReferencesJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Loaders\XML\LiteratureReferencesLoader;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers\MetadataFiller;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers\ExtenderWithAggregatedData;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers\ExtenderWithAggregatedSearchableData;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers\ExtenderWithSubPublications;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers\MapToSearchableLiteratureReference;

final class LiteratureReferences
{
    private LiteratureReferencesLoader $loader;

    private function __construct(Paths $paths)
    {
        $literatureReferenceOutputFilepath = $paths->getOutputPath('cda-literaturereferences-v2.json');
        $literatureReferenceElasticsearchOutputFilepath = $paths->getElasticsearchOutputPath('cda-literaturereferences-v2.bulk');

        $this->loader = LiteratureReferencesLoader::withSourcesAt($paths->getLiteratureReferencesInputFilePaths());
        $this->loader->pipeline(
            MetadataFiller::new(),
            ExtenderWithAggregatedData::new(),
            ExtenderWithSubPublications::new()
                /* Exporting the literatureReferences as JSON */
                ->pipeline(
                    LiteratureReferencesJSONLangExporter::withDestinationAt($literatureReferenceOutputFilepath),
                )
                /* Exporting the literatureReferences for Elasticsearch bulk import */
                ->pipeline(
                    MapToSearchableLiteratureReference::new()
                        ->pipeline(
                            ExtenderWithAggregatedSearchableData::new(),
                            LiteratureReferencesElasticsearchLangExporter::withDestinationAt($literatureReferenceElasticsearchOutputFilepath),
                        ),
                ),
        );
    }

    public static function new(Paths $paths): self
    {
        return new self($paths);
    }

    public function run(): self
    {
        $this->loader->run();
        return $this;
    }
}
