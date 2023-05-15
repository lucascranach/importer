<?php

namespace CranachDigitalArchive\Importer\Modules\Filters\Exporters;

use Error;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\LangFilterContainer;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Filters exporter on a json flat file base
 */
class FilterJSONLangExporter extends Consumer implements IFileExporter
{
    private $fileExt = 'json';
    private $filename = null;
    private $dirname = null;
    private $done = false;
    private $langFiltetContainers = [];


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
            throw new Error('No filepath for JSON graphics export set!');
        }

        return $exporter;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof LangFilterContainer)) {
            throw new Error('Pushed item is not of expected class \'LangFilterContainer\'');
        }

        if ($this->done) {
            throw new Error('Can\'t push more items after done() was called!');
        }

        $this->langFiltetContainers[] = $item;

        return true;
    }


    /**
     * @return void
     */
    public function done(IProducer $producer)
    {
        $this->done = true;
        $categorizedItems = $this->categorizeItemsByLangCode($this->langFiltetContainers);
        $this->outputToFile($categorizedItems);
        $this->langFiltetContainers = [];
    }

    /**
     * @return void
     */
    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }


    private function outputToFile(array $langItems): bool
    {
        foreach ($langItems as $langCode => $items) {
            $filename = $this->filename . '.' . $langCode . '.' . $this->fileExt;
            $destFilepath = $this->dirname . DIRECTORY_SEPARATOR . $filename;

            if (!file_exists($this->dirname)) {
                @mkdir($this->dirname, 0777, true);
            }

            $data = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($destFilepath, $data);
        }

        return true;
    }


    private function categorizeItemsByLangCode(array $langFiltetContainers): array
    {
        $arr = [];

        foreach ($langFiltetContainers as $container) {
            $langCode = $container->getLang();
            if (!isset($arr[$langCode])) {
                $arr[$langCode] = [];
            }

            $filter = $container->getFilter();
            $arr[$langCode][] = $filter;
        }

        return $arr;
    }
}
