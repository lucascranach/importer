<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Exporters;

use Error;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Painting;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search\SearchablePaintingLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Paintings exporter for ElasticSearch Bulk Format ('json')
 * - one file per language
 */
class PaintingsElasticsearchLangExporter extends Consumer implements IFileExporter
{
    private $fileExt = 'bulk';
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
            throw new Error('No filepath for JSON paintings export set!');
        }

        return $exporter;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof SearchablePaintingLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'SearchablePaintingLanguageCollection\'');
        }

        if ($this->done) {
            throw new Error('Can\'t push more items after done() was called!');
        }

        return $this->handleCollection($item);
    }


    /**
     * @return void
     */
    public function done(ProducerInterface $producer)
    {
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


    private function handleCollection(SearchablePaintingLanguageCollection $collection): bool
    {
        $retVal = true;

        foreach ($collection as $langCode => $painting) {
            $retVal = $retVal && $this->appendItemToOutputFile($langCode, $painting);
        }

        return $retVal;
    }


    private function appendItemToOutputFile(string $langCode, Painting $item): bool
    {
        if (!isset($this->outputFilesByLangCode[$langCode])) {
            $this->outputFilesByLangCode[$langCode] = [
                "path" => $this->initializeOutputFileForLangCode($langCode),
            ];
        }

        $index = [
            'index' => [
                '_id' => $item->getInventoryNumber(),
            ],
        ];
        $indexStringified = json_encode($index, JSON_UNESCAPED_UNICODE);
        $itemStringified = json_encode($item, JSON_UNESCAPED_UNICODE);

        $itemBundleStringified = $indexStringified . "\n" . $itemStringified . "\n";

        file_put_contents($this->outputFilesByLangCode[$langCode]['path'], $itemBundleStringified, FILE_APPEND);
        return true;
    }


    private function initializeOutputFileForLangCode(string $langCode): string
    {
        $filename = $this->filename . '.' . $langCode . '.' . $this->fileExt;
        $destFilepath = $this->dirname . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($this->dirname)) {
            @mkdir($this->dirname, 0777, true);
        }

        file_put_contents($destFilepath, '');

        return $destFilepath;
    }
}
