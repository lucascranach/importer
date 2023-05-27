<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ThesaurusMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Loaders\Memory\ThesaurusLoader as ThesaurusMemoryLoader;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Loaders\XML\ThesaurusLoader as ThesaurusXMLLoader;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ThesaurusJSONExporter;

final class Thesaurus
{
    private ThesaurusMemoryExporter $thesaurusMemoryExporter;
    private ThesaurusXMLLoader $loader;

    private function __construct(Paths $paths)
    {
        $thesaurusOutputFilepath = $paths->getOutputPath('cda-thesaurus-v2.json');

        /* Thesaurus */
        $this->thesaurusMemoryExporter = ThesaurusMemoryExporter::new(); /* needed later for graphics and paintings */
        ThesaurusMemoryLoader::withMemory($this->thesaurusMemoryExporter);

        $this->loader = ThesaurusXMLLoader::withSourcesAt($paths->getThesaurusInputFilePaths());
        $this->loader
            ->pipeline(ThesaurusJSONExporter::withDestinationAt($thesaurusOutputFilepath))
            ->pipeline($this->thesaurusMemoryExporter);
    }

    public static function new(Paths $paths): self
    {
        return new self($paths);
    }

    public function getMemoryExporter(): ThesaurusMemoryExporter
    {
        return $this->thesaurusMemoryExporter;
    }

    public function run(): self
    {
        $this->loader->run();
        return $this;
    }

    public function cleanUp(): void
    {
        $this->thesaurusMemoryExporter->cleanUp();
    }
}
