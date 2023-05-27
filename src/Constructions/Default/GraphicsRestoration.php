<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Modules\Restorations\Exporters\RestorationsMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Restorations\Loaders\XML\RestorationsLoader;

final class GraphicsRestoration
{
    private RestorationsMemoryExporter $restorationsMemoryExporter;
    private RestorationsLoader $loader;

    private function __construct(Paths $paths)
    {
        $this->restorationsMemoryExporter = RestorationsMemoryExporter::new();

        $this->loader = RestorationsLoader::withSourcesAt($paths->getGraphicsRestorationInputFilePaths());
        $this->loader->pipeline($this->restorationsMemoryExporter);
    }

    public static function new(Paths $paths): self
    {

        return new self($paths);
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
