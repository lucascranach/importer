<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus\Loaders\Memory;

use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ThesaurusMemoryExporter;

/**
 * Thesaurus loader on a memory base
 */
class ThesaurusLoader extends Producer
{
    private $thesaurusMemory = null;

    private function __construct()
    {
    }


    /**
     * @return self
     */
    public static function withMemory(ThesaurusMemoryExporter $thesaurusMemory)
    {
        $loader = new self;
        $loader->thesaurusMemory = $thesaurusMemory;

        return $loader;
    }


    /**
     * @return void
     */
    public function run()
    {
        echo "Processing memory thesaurus file\n";

        $this->next($this->thesaurusMemory->getData());

        /* Signaling that we are done reading the memory exporter */
        $this->notifyDone();
    }
}
