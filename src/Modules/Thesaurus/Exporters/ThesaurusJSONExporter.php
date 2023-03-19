<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\Thesaurus;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Graphics restoration exporter on a json flat file base
 */
class ThesaurusJSONExporter extends Consumer implements IFileExporter
{
    private $destFilepath = null;
    private $item = null;
    private $done = false;


    private function __construct()
    {
    }


    /**
     * @return self
     */
    public static function withDestinationAt(string $destFilepath)
    {
        $exporter = new self();
        $exporter->destFilepath = $destFilepath;
        return $exporter;
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


    public function isDone(): bool
    {
        return $this->done;
    }


    /**
     * @return void
     */
    public function done(ProducerInterface $producer)
    {
        if (is_null($this->destFilepath)) {
            throw new \Error('No filepath for JSON thesaurus export set!');
        }

        $data = json_encode($this->item, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $dirname = dirname($this->destFilepath);

        if (!file_exists($dirname)) {
            @mkdir($dirname, 0777, true);
        }

        file_put_contents($this->destFilepath, $data);

        $this->done = true;
        $this->item = null;
    }


    /**
     * @return void
     */
    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }
}
