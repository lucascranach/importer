<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\CustomFiltersMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Filters\Loaders\JSON\CustomFiltersLoader;

final class MemoryFilters
{
    private CustomFiltersMemoryExporter $memoryExporter;
    private CustomFiltersLoader $loader;

    private function __construct(Paths $paths)
    {
        /* Loading filters into memory */
        $this->memoryExporter = CustomFiltersMemoryExporter::new(); /* needed later for graphics and paintings */

        $this->loader = CustomFiltersLoader::withSourceAt($paths->getResourcesPath('custom_filters.json'));
        $this->loader->pipeline($this->memoryExporter);
    }

    public static function new(Paths $paths): self
    {
        return new self($paths);
    }

    public function getMemoryExporter(): CustomFiltersMemoryExporter
    {
        return $this->memoryExporter;
    }

    public function run(): self
    {
        $this->loader->run();
        return $this;
    }

    public function cleanUp(): void
    {
        $this->memoryExporter->cleanUp();
    }
}
