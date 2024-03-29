<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReference;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReferenceLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * LiteratureReferences exporter on a json flat file base
 */
class LiteratureReferencesJSONLangExporter extends Consumer implements IFileExporter
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

        $splitFileName = array_map('trim', explode('.', $filename));

        if (count($splitFileName) === 2 && strlen($splitFileName[1])) {
            $exporter->fileExt = $splitFileName[1];
        }

        $exporter->filename = $splitFileName[0];

        if (empty($exporter->dirname) || empty($exporter->filename)) {
            throw new Error('No filepath for JSON literature export set!');
        }

        return $exporter;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof LiteratureReferenceLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'LiteratureReferenceLanguageCollection\'');
        }

        if ($this->done) {
            throw new \Error('Can\'t push more items after done() was called!');
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


    private function handleCollection(LiteratureReferenceLanguageCollection $collection): bool
    {
        $retVal = true;

        foreach ($collection as $langCode => $literatureReference) {
            $retVal = $retVal && $this->appendItemToOutputFile($langCode, $literatureReference);
        }

        return $retVal;
    }


    private function appendItemToOutputFile(string $langCode, LiteratureReference $item): bool
    {
        $key = $langCode;

        if (!isset($this->outputFilesByLangCode[$key])) {
            $this->outputFilesByLangCode[$key] = [
                'path' => $this->initializeOutputFileForLangCode($key),
                'isFirstItem' => true,
            ];
        }


        $delimiter = ',';

        if ($this->outputFilesByLangCode[$key]['isFirstItem']) {
            $delimiter = '';
            $this->outputFilesByLangCode[$key]['isFirstItem'] = false;
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

        file_put_contents($this->outputFilesByLangCode[$key]['path'], $entryData, FILE_APPEND);
        return true;
    }


    private function initializeOutputFileForLangCode($key): string
    {
        $filename = $this->filename . '.' . $key . '.' . $this->fileExt;
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
