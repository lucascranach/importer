<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Exporters;

use Error;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Modules\Drawings\Interfaces\IDrawing;
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\DrawingLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Drawings exporter on a json flat file base
 * - one file per language
 * - linked works
 */
class DrawingsJSONLangExporter extends Consumer implements IFileExporter
{
    private $fileExt = 'json';
    private $filename = null;
    private $dirname = null;
    private $outputFilesByLangCode = [];
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

        $filename = basename($destFilepath);
        $exporter->dirname = trim(dirname($destFilepath));

        $splitFilename = array_map('trim', explode('.', $filename));

        if (count($splitFilename) === 2 && strlen($splitFilename[1])) {
            $exporter->fileExt = $splitFilename[1];
        }

        $exporter->filename = $splitFilename[0];

        if (empty($exporter->dirname) || empty($exporter->filename)) {
            throw new Error('No filepath for JSON drawings export set!');
        }

        return $exporter;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof DrawingLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'DrawingLanguageCollection\'');
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
        $this->closeAllOutputFiles();
        $this->done = true;
        $this->outputFilesByLangCode = [];
    }

    /**
     * @return void
     */
    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }


    private function handleCollection(DrawingLanguageCollection $collection): bool
    {
        $retVal = true;

        /** @var string $langCode */
        /** @var IDrawing $drawing */
        foreach ($collection as $langCode => $drawing) {
            $retVal = $retVal && $this->appendItemToOutputFile($langCode, $drawing);
        }

        return $retVal;
    }


    private function appendItemToOutputFile(string $langCode, IDrawing $item): bool
    {
        if (!isset($this->outputFilesByLangCode[$langCode])) {
            $this->outputFilesByLangCode[$langCode] = [
                "path" => $this->initializeOutputFileForLangCode($langCode),
                "isFirstItem" => true,
            ];
        }

        $delimiter = ',';

        if ($this->outputFilesByLangCode[$langCode]['isFirstItem']) {
            $delimiter = '';
            $this->outputFilesByLangCode[$langCode]['isFirstItem'] = false;
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

        file_put_contents($this->outputFilesByLangCode[$langCode]['path'], $entryData, FILE_APPEND);
        return true;
    }


    private function initializeOutputFileForLangCode(string $langCode): string
    {
        $filename = $this->filename . '.' . $langCode . '.' . $this->fileExt;
        $destFilepath = $this->dirname . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($this->dirname)) {
            @mkdir($this->dirname, 0777, true);
        }

        file_put_contents($destFilepath, "{\n    \"items\": [");

        return $destFilepath;
    }


    private function closeAllOutputFiles(): bool
    {
        foreach ($this->outputFilesByLangCode as $file) {
            file_put_contents($file['path'], "\n    ]\n}", FILE_APPEND);
        }

        return true;
    }
}
