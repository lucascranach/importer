<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Restoration;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Graphics restoration exporter on a json flat file base
 */
class RestorationsJSONExporter extends Consumer implements IFileExporter
{
    private $destFilepath = null;
    private $items = [];
    private $done = false;


    private function __construct()
    {
    }


    public static function withDestinationAt(string $destFilepath)
    {
        $exporter = new self();
        $exporter->destFilepath = $destFilepath;
        return $exporter;
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


    public function isDone(): bool
    {
        return $this->done;
    }


    public function done(ProducerInterface $producer)
    {
        if (is_null($this->destFilepath)) {
            throw new \Error('No filepath for JSON restoration export set!');
        }

        $data = json_encode(array('items' => $this->items), JSON_PRETTY_PRINT);
        $dirname = dirname($this->destFilepath);

        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        }

        file_put_contents($this->destFilepath, $data);

        $this->done = true;
        $this->items = [];
    }


    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }
}
