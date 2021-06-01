<?php

namespace CranachDigitalArchive\Importer\Modules\Filters\Exporters;

use Error;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\Filter;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Filters exporter on a json flat file base
 */
class FilterExporter extends Consumer implements IFileExporter
{
    private $destFilepath;
    private $done = false;
    private $items;


    public function __construct()
    {
    }


    /**
     * @return self
     */
    public static function withDestinationAt(string $destFilepath)
    {
        $exporter = new self;

        $exporter->destFilepath = $destFilepath;

        if (empty($exporter->destFilepath)) {
            throw new Error('No filepath for JSON filter export set!');
        }

        return $exporter;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof Filter)) {
            throw new Error('Pushed item is not of expected class \'Filter\'');
        }

        if ($this->done) {
            throw new Error('Can\'t push more items after done() was called!');
        }

        $this->items[] = $item;

        return true;
    }


    /**
     * @return void
     */
    public function done(ProducerInterface $producer)
    {
        $this->done = true;
        $this->outputToFile($this->items);
    }

    /**
     * @return void
     */
    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }


    private function outputToFile(array$items): bool
    {
        $data = json_encode($items, JSON_PRETTY_PRINT);

        file_put_contents($this->destFilepath, $data);
        return true;
    }
}
