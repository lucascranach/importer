<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IMemoryExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\Thesaurus;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Thesaurus in memory exporter
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


    public function findByFields(array $fieldValues)
    {
        if (!$this->isDone()) {
            throw new Error('Can not return thesaurus data if not done!');
        }

        $items = !is_null($this->item) ? [$this->item] : [];

        foreach ($items as $item) {
            $matching = true;

            foreach ($fieldValues as $fieldName => $value) {
                $matching = $matching && isset($item->{$fieldName}) && $item->{$fieldName} === $value;
            }

            if ($matching) {
                return $item;
            }
        }

        return null;
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
