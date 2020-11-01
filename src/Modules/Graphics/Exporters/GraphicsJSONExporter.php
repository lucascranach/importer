<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Graphics exporter on a json flat file base
 */
class GraphicsJSONExporter extends Consumer implements IFileExporter
{
    private $destFilepath = null;
    private $destFileInitialized = false;
    private $isFirstItem = true;
    private $done = false;


    private function __construct()
    {
    }


    public static function withDestinationAt(string $destFilepath)
    {
        $exporter = new self;
        $exporter->destFilepath = $destFilepath;

        return $exporter;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof Graphic)) {
            throw new Error('Pushed item is not of expected class \'Graphic\'');
        }

        if ($this->done) {
            throw new Error('Can\'t push more items after done() was called!');
        }

        return $this->appendItemToOutputFile($item);
    }


    public function done(ProducerInterface $producer)
    {
        $this->closeOutputFile();
        $this->done = true;
        $this->destFileInitialized = false;
        $this->isFirstItem = true;
    }


    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }


    private function appendItemToOutputFile(Graphic $item): bool
    {
        if (!$this->destFileInitialized) {
            $this->initializeOutputFile();
            $this->destFileInitialized = true;
        }

        $delimiter = ',';

        if ($this->isFirstItem) {
            $delimiter = '';
            $this->isFirstItem = false;
        }

        $data = json_encode($item, JSON_PRETTY_PRINT);
        $data = implode(
            "\n",
            array_map(
                function ($line) {
                    return '        ' . $line;
                },
                explode("\n", $data),
            ),
        );

        $entryData = $delimiter . "\n" . $data;

        file_put_contents($this->destFilepath, $entryData, FILE_APPEND);
        return true;
    }


    private function initializeOutputFile(): string
    {
        $dirname = dirname($this->destFilepath);

        if (!file_exists($this->destFilepath)) {
            @mkdir($dirname, 0777, true);
        }

        file_put_contents($this->destFilepath, "{\n    \"items\": [");

        return $this->destFilepath;
    }


    private function closeOutputFile(): bool
    {
        file_put_contents($this->destFilepath, "\n    ]\n}", FILE_APPEND);

        return true;
    }
}
