<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Graphics\Interfaces\IGraphic;
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
        if (!($item instanceof GraphicLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'GraphicLanguageCollection\'');
        }

        if ($this->done) {
            throw new Error('Can\'t push more items after done() was called!');
        }

        return $this->handleCollection($item);
    }


    /**
     * @return void
     */
    public function done(IProducer $producer)
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


    private function handleCollection(GraphicLanguageCollection $collection): bool
    {
        $retVal = true;

        foreach ($collection as $graphic) {
            $retVal = $retVal && $this->appendItemToOutputFile($graphic);
        }

        return $retVal;
    }


    private function appendItemToOutputFile(IGraphic $item): bool
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
