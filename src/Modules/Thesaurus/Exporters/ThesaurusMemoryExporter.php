<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IMemoryExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\Thesaurus;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Graphics restoration exporter on a json flat file base
 */
class ThesaurusMemoryExporter extends Consumer implements IMemoryExporter
{
    private $item = null;
    private $done = false;


    private function __construct()
    {
    }


    public static function new()
    {
        return new self();
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof Thesaurus)) {
            throw new Error('Pushed item is not of expected class \'Thesaurus\'');
        }

        if ($this->done) {
            throw new \Error('Can\'t push more items after done() was called!');
        }

        $this->item = $item;

        return true;
    }


    public function getData(): Thesaurus
    {
        if (!$this->isDone()) {
            throw new Error('Can not return thesaurus data if not done!');
        }

        return $this->item;
    }

    public function cleanUp()
    {
        $this->item = null;
    }


    public function isDone(): bool
    {
        return $this->done;
    }


    public function done(ProducerInterface $producer)
    {
        $this->done = true;
    }


    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }
}
