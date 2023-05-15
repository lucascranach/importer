<?php

namespace CranachDigitalArchive\Importer\Modules\Filters\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IMemoryExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\CustomFilter;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * CustomFilters in memory exporter
 */
class CustomFiltersMemoryExporter extends Consumer implements IMemoryExporter
{
    private $items = null;
    private $done = false;


    private function __construct()
    {
    }


    public static function new(): self
    {
        return new self();
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof CustomFilter)) {
            throw new Error('Pushed item is not of expected class \'CustomFilter\'');
        }

        if ($this->done) {
            throw new \Error('Can\'t push more items after done() was called!');
        }

        $this->items[] = $item;

        return true;
    }


    public function getData(): ?array
    {
        if (!$this->isDone()) {
            throw new Error('Can not return custom filter data if not done!');
        }

        return $this->items;
    }


    public function findByFields(array $fieldValues)
    {
        if (!$this->isDone()) {
            throw new Error('Can not return custom filter data if not done!');
        }

        $items = !is_null($this->items) ? $this->items : [];

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


    /**
     * @return void
     */
    public function cleanUp()
    {
        $this->items = null;
    }


    public function isDone(): bool
    {
        return $this->done;
    }


    /**
     * @return void
     */
    public function done(IProducer $producer)
    {
        $this->done = true;
    }


    /**
     * @return void
     */
    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }
}
