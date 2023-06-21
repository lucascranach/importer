<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Modules\Restorations\Exporters\RestorationsMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Restorations\Loaders\XML\RestorationsLoader;
use CranachDigitalArchive\Importer\Modules\Restorations\Transformers\ExtenderWithIds;

final class PaintingsRestoration
{
    private RestorationsMemoryExporter $restorationsMemoryExporter;
    private RestorationsLoader $loader;

    private function __construct(Paths $paths, MemoryArtefactFilters $memoryArtefactFilters)
    {
        $this->restorationsMemoryExporter = RestorationsMemoryExporter::new();
        $restorationsIdAdder = ExtenderWithIds::new($memoryArtefactFilters->getMemoryExporter());

        $this->loader = RestorationsLoader::withSourcesAt($paths->getPaintingsRestorationInputFilePaths());
        $this->loader->pipeline(
            $restorationsIdAdder,
            $this->restorationsMemoryExporter,
        );
    }

    public static function new(Paths $paths, MemoryArtefactFilters $memoryArtefactFilters): self
    {
        return new self($paths, $memoryArtefactFilters);
    }

    public function getMemoryExporter(): RestorationsMemoryExporter
    {
        return $this->restorationsMemoryExporter;
    }

    public function run(): self
    {
        $this->loader->run();
        return $this;
    }

    public function cleanUp(): void
    {
        $this->restorationsMemoryExporter->cleanUp();
    }
}
