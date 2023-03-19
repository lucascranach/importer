<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReference;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * LiteratureReferences exporter on a json flat file base
 */
class LiteratureReferencesJSONExporter extends Consumer implements IFileExporter
{
    private $destFilepath = null;
    private $destFileInitialized = false;
    private $isFirstItem = true;
    private $done = false;


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

        return $exporter;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof LiteratureReference)) {
            throw new Error('Pushed item is not of expected class \'LiteratureReference\'');
        }

        if ($this->done) {
            throw new \Error('Can\'t push more items after done() was called!');
        }

        return $this->appendItemToOutputFile($item);
    }


    /**
     * @return void
     */
    public function done(ProducerInterface $producer)
    {
        $this->closeOutputFile();
        $this->done = true;
        $this->destFileInitialized = false;
        $this->isFirstItem = true;
    }


    /**
     * @return void
     */
    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }


    private function appendItemToOutputFile(LiteratureReference $item): bool
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

        $data = json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
