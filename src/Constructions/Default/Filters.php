<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\FilterJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Filters\Loaders\Memory\CustomFiltersAndThesaurusLoader as MemoryCustomFiltersAndThesaurusLoader;
use CranachDigitalArchive\Importer\Modules\Filters\Loaders\Memory\CustomFiltersLoader as MemoryCustomFiltersLoader;
use CranachDigitalArchive\Importer\Modules\Filters\Transformers\ArtefactAlphabeticSorter;
use CranachDigitalArchive\Importer\Modules\Filters\Transformers\ArtefactNumericalSorter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ReducedThesaurusMemoryExporter;

final class Filters
{
    private string $artefactFiltersOutputFilepath;
    private string $literatureReferenceFiltersOutputFilepath;
    private Base $base;
    private Thesaurus $thesaurus;
    private MemoryFilters $memoryFilters;

    private function __construct(
        Paths $paths,
        Base $base,
        Thesaurus $thesaurus,
        MemoryFilters $memoryFilters,
    ) {
        $this->artefactFiltersOutputFilepath = $paths->getOutputPath('filters/cda-artefact-filters.json');
        $this->literatureReferenceFiltersOutputFilepath = $paths->getOutputPath('filters/cda-literaturereference-filters.json');

        $this->base = $base;
        $this->thesaurus = $thesaurus;
        $this->memoryFilters = $memoryFilters;
    }

    public static function new(
        Paths $paths,
        Base $base,
        Thesaurus $thesaurus,
        MemoryFilters $memoryFilters,
    ): self {
        return new self(
            $paths,
            $base,
            $thesaurus,
            $memoryFilters,
        );
    }

    public function run(): self
    {
        /* == Artefacts */
        /* Needs to be initialized and ran after all meta references could be collected;
         * after paintings and graphics were processed
         */
        $restrictedTermIds = array_map(
            function ($metaReference) {
                return $metaReference->getTerm();
            },
            array_values($this->base->getMetaReferenceCollector()->getCollection()),
        );

        MemoryCustomFiltersAndThesaurusLoader::withMemory(
            $this->memoryFilters->getArtefactMemoryExporter(),
            ReducedThesaurusMemoryExporter::new($this->thesaurus->getMemoryExporter(), $restrictedTermIds),
        )->pipeline(
            ArtefactNumericalSorter::new(),
            ArtefactAlphabeticSorter::new(),
            FilterJSONLangExporter::withDestinationAt($this->artefactFiltersOutputFilepath),
        )->run();

        /* == LiteratureReferences */
        MemoryCustomFiltersLoader::withMemory(
            $this->memoryFilters->getLiteratureReferenceMemoryExporter(),
        )->pipeline(
            FilterJSONLangExporter::withDestinationAt($this->literatureReferenceFiltersOutputFilepath),
        )->run();

        return $this;
    }
}
