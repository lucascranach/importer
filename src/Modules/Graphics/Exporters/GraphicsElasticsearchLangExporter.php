<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search\SearchableGraphicLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Graphics exporter on a json flat file base (one file per language)
 */
class GraphicsElasticsearchLangExporter extends Consumer implements IFileExporter
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
        if (!($item instanceof SearchableGraphicLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'SearchableGraphicLanguageCollection\'!');
        }

        if ($this->done) {
            throw new Error('Can\'t push more items after done() was called!');
        }

        foreach ($item as $langCode => $graphic) {
            if (!isset($this->langBuckets[$langCode])) {
                $this->langBuckets[$langCode] = (object) [
                    'items' => [],
                ];
            }

            $this->langBuckets[$langCode]->items[] = $graphic;
        }

        return true;
    }


    /**
     * @return void
     */
    public function done(IProducer $producer)
    {
        if (is_null($this->dirname) || empty($this->dirname)
         || is_null($this->filename) || empty($this->filename)) {
            throw new Error('No filepath for JSON graphics export set!');
        }

        foreach ($this->langBuckets as $langCode => $bucket) {
            $existenceTypes = array_reduce(
                $bucket->items,
                function ($carry, $item) {
                    $existenceTypeKey = $item->getIsVirtual() ? 'virtual' : 'real';
                    $carry[$existenceTypeKey][] = $item;
                    return $carry;
                },
                [ "virtual" => [], "real" => [] ],
            );

            foreach ($existenceTypes as $existenceTypeKey => $existenceTypeItems) {
                $filename = implode('.', [
                    $this->filename,
                    $existenceTypeKey,
                    $langCode,
                    $this->fileExt,
                ]);
                $destFilepath = $this->dirname . DIRECTORY_SEPARATOR . $filename;

                if (!file_exists($this->dirname)) {
                    @mkdir($this->dirname, 0777, true);
                }

                file_put_contents($destFilepath, '');

                foreach ($existenceTypeItems as $existenceTypeItem) {
                    $index = [
                        'index' => [
                            '_id' => $existenceTypeItem->getInventoryNumber(),
                        ],
                    ];
                    $indexStringified = json_encode($index, JSON_UNESCAPED_UNICODE);
                    $itemStringified = json_encode($existenceTypeItem, JSON_UNESCAPED_UNICODE);

                    $itemBundleStringified = $indexStringified . "\n" . $itemStringified . "\n";

                    file_put_contents($destFilepath, $itemBundleStringified, FILE_APPEND);
                }
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
