<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IMemoryExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Restoration;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Graphics restoration exporter on a json flat file base
 */
class RestorationsMemoryExporter extends Consumer implements IMemoryExporter
{
    private $items = [];
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
        if (!($item instanceof Restoration)) {
            throw new Error('Pushed item is not of expected class \'Restoration\'');
        }

        if ($this->done) {
            throw new \Error('Can\'t push more items after done() was called!');
        }

        $this->items[] = $item;

        return true;
    }


    public function getData(): array
    {
        if (!$this->isDone()) {
            throw new Error('Can not return restoration data if not done!');
        }

        return $this->items;
    }


    public function findByFields(array $fieldValues): ?Restoration
    {
        if (!$this->isDone()) {
            throw new Error('Can not return thesaurus data if not done!');
        }

        foreach ($this->items as $item) {
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
    public function done(ProducerInterface $producer)
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
