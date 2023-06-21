<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\CustomFiltersMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Filters\Loaders\JSON\CustomFiltersLoader;

final class MemoryFilters
{
    private CustomFiltersMemoryExporter $artefactMemoryExporter;
    private CustomFiltersLoader $artefactLoader;
    private CustomFiltersMemoryExporter $literatureReferenceMemoryExporter;
    private CustomFiltersLoader $literatureReferenceLoader;

    private function __construct(Paths $paths)
    {
        /* Loading filters into memory */

        /* == Artefacts */
        $this->artefactMemoryExporter = CustomFiltersMemoryExporter::new(); /* needed later for graphics and paintings */

        $this->artefactLoader = CustomFiltersLoader::withSourceAt(
            $paths->getResourcesPath('custom_artefact_filters.json'),
        );
        $this->artefactLoader->pipeline($this->artefactMemoryExporter);


        /* == LiteratureReferences */
        $this->literatureReferenceMemoryExporter = CustomFiltersMemoryExporter::new(); /* needed later for literature references */

        $this->literatureReferenceLoader = CustomFiltersLoader::withSourceAt(
            $paths->getResourcesPath('custom_literaturereferences_filters.json'),
        );
        $this->literatureReferenceLoader->pipeline($this->literatureReferenceMemoryExporter);
    }

    public static function new(Paths $paths): self
    {
        return new self($paths);
    }

    public function getArtefactMemoryExporter(): CustomFiltersMemoryExporter
    {
        return $this->artefactMemoryExporter;
    }

    public function getLiteratureReferenceMemoryExporter(): CustomFiltersMemoryExporter
    {
        return $this->literatureReferenceMemoryExporter;
    }

    public function run(): self
    {
        $this->artefactLoader->run();
        $this->literatureReferenceLoader->run();
        return $this;
    }

    public function cleanUp(): void
    {
        $this->artefactMemoryExporter->cleanUp();
        $this->literatureReferenceMemoryExporter->cleanUp();
    }
}
