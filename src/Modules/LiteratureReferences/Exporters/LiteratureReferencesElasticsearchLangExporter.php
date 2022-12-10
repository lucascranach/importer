<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReference;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * LiteratureReferences exporter on a json flat file base (one file per language)
 */
class LiteratureReferencesElasticsearchLangExporter extends Consumer implements IFileExporter
{
    private $fileExt = 'bulk';
    private $filename = null;
    private $dirname = null;
    private $langBuckets = [];
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
        if (!($item instanceof LiteratureReference)) {
            throw new Error('Pushed item is not of expected class \'LiteratureReference\'!');
        }

        if ($this->done) {
            throw new Error('Can\'t push more items after done() was called!');
        }

        $metadata = $item->getMetadata();
        $langCode = !is_null($metadata) ? $metadata->getLangCode() : 'unknown';

        if (!isset($this->langBuckets[$langCode])) {
            $this->langBuckets[$langCode] = (object) [
                'items' => [],
            ];
        }

        $this->langBuckets[$langCode]->items[] = $item;

        return true;
    }


    /**
     * @return void
     */
    public function done(ProducerInterface $producer)
    {
        if (is_null($this->dirname) || empty($this->dirname)
         || is_null($this->filename) || empty($this->filename)) {
            throw new Error('No filepath for JSON archivals export set!');
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
                        '_id' => $item->getReferenceId(),
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


    /**
     * @return void
     */
    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }
}
