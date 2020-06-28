<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Exporters;

use Error;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Painting;
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
    private $langBuckets = [];
    private $done = false;


    public function __construct()
    {
    }


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

        return $exporter;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof Painting)) {
            throw new Error('Pushed item is not of expected class \'Painting\'');
        }

        if ($this->done) {
            throw new Error('Can\'t push more items after done() was called!');
        }

        if (!isset($this->langBuckets[$item->getLangCode()])) {
            $this->langBuckets[$item->getLangCode()] = (object) [
                'items' => [],
            ];
        }

        $this->langBuckets[$item->getLangCode()]->items[] = $item;

        return true;
    }


    public function done(ProducerInterface $producer)
    {
        if (is_null($this->dirname) || empty($this->dirname)
         || is_null($this->filename) || empty($this->filename)) {
            throw new Error('No filepath for JSON paintings export set!');
        }

        foreach ($this->langBuckets as $langCode => $bucket) {
            $filename = implode('.', [
                $this->filename,
                $langCode,
                $this->fileExt,
            ]);
            $destFilepath = $this->dirname . DIRECTORY_SEPARATOR . $filename;

            if (!file_exists($this->dirname)) {
                @mkdir($this->dirname, 0777, true);
            }

            file_put_contents($destFilepath, '');

            foreach ($bucket->items as $item) {
                $index = [
                    'index' => [
                        '_id' => $item->getInventoryNumber(),
                    ],
                ];
                $indexStringified = json_encode($index);
                $itemStringified = json_encode($item);

                $itemBundleStringified = $indexStringified . "\n" . $itemStringified . "\n";

                file_put_contents($destFilepath, $itemBundleStringified, FILE_APPEND);
            }
        }

        $this->done = true;

        $this->langBuckets = [];
    }

    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }
}
