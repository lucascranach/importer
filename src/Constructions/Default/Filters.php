<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\CustomFiltersMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\FilterJSONLangExporter;
use CranachDigitalArchive\Importer\Modules\Filters\Loaders\Memory\CustomFiltersAndThesaurusLoader as MemoryCustomFiltersAndThesaurusLoader;
use CranachDigitalArchive\Importer\Modules\Filters\Transformers\AlphabeticSorter;
use CranachDigitalArchive\Importer\Modules\Filters\Transformers\NumericalSorter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ReducedThesaurusMemoryExporter;

final class Filters
{
    private string $filtersOutputFilepath;
    private Base $base;
    private Thesaurus $thesaurus;
    private MemoryFilters $memoryFilters;
    private CustomFiltersMemoryExporter $memoryExporter;

    private function __construct(Paths $paths, Base $base, Thesaurus $thesaurus, MemoryFilters $memoryFilters)
    {
        $this->filtersOutputFilepath = $paths->getOutputPath('cda-filters.json');

        $this->base = $base;
        $this->thesaurus = $thesaurus;
        $this->memoryFilters = $memoryFilters;
    }

    public static function new(Paths $paths, Base $base, Thesaurus $thesaurus, MemoryFilters $memoryFilters): self
    {
        return new self($paths, $base, $thesaurus, $memoryFilters);
    }

    public function getMemoryExporter(): CustomFiltersMemoryExporter
    {
        return $this->memoryExporter;
    }

    public function run(): self
    {
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
            $this->memoryFilters->getMemoryExporter(),
            ReducedThesaurusMemoryExporter::new($this->thesaurus->getMemoryExporter(), $restrictedTermIds),
        )->pipeline(
            NumericalSorter::new(),
            AlphabeticSorter::new(),
            FilterJSONLangExporter::withDestinationAt($this->filtersOutputFilepath),
        )->run();

        return $this;
    }

    public function cleanUp(): void
    {
        $this->memoryExporter->cleanUp();
    }
}
