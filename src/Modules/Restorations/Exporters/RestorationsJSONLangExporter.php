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
class RestorationsJSONLangExporter extends Consumer implements IFileExporter
{
    private $fileExt = 'json';
    private $filename = null;
    private $dirname = null;
    private $outputFilesByLangCode = [];
    private $done = false;


    private function __construct()
    {
    }


    public static function withDestinationAt(string $destFilepath)
    {
        $exporter = new self();

        $filename = basename($destFilepath);
        $exporter->dirname = trim(dirname($destFilepath));

        $splitFilename = array_map('trim', explode('.', $filename));

        if (count($splitFilename) === 2 && strlen($splitFilename[1])) {
            $exporter->fileExt = $splitFilename[1];
        }

        $exporter->filename = $splitFilename[0];

        if (is_null($exporter->dirname) || empty($exporter->dirname)
            || is_null($exporter->filename) || empty($exporter->filename)) {
            throw new Error('No filepath for JSON restorations export set!');
        }

        return $exporter;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof Restoration)) {
            throw new Error('Pushed item is not of expected class \'Restoration\'');
        }

        if ($this->done) {
            throw new Error('Can\'t push more items after done() was called!');
        }

        return $this->appendItemToOutputFile($item);
    }



    public function done(ProducerInterface $producer)
    {
        $this->closeAllOutputFiles();
        $this->done = true;
        $this->outputFilesByLangCode = [];
    }


    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }

    private function appendItemToOutputFile(Restoration $item): bool
    {
        $langCode = $item->getLangCode();

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

        file_put_contents($this->outputFilesByLangCode[$langCode]['path'], $entryData, FILE_APPEND);
        return true;
    }


    private function initializeOutputFileForLangCode(string $langCode): string
    {
        $filename = $this->filename . '.' . $langCode . '.' . $this->fileExt;
        $destFilepath = $this->dirname . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($this->dirname)) {
            mkdir($this->dirname, 0777, true);
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
