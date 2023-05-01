<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Graphics\Interfaces\IGraphic;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Graphics exporter on a json flat file base
 * - one file per language
 * - linked works
 */
class GraphicsJSONLangExistenceTypeExporter extends Consumer implements IFileExporter
{
    private $fileExt = 'json';
    private $filename = null;
    private $dirname = null;
    private $outputFilesByLangCode = [];
    private $objectsWithMissingReferencesList = [];
    private $inventoryNumberList = [];
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

        if (empty($exporter->dirname) || empty($exporter->filename)) {
            throw new Error('No filepath for JSON graphics export set!');
        }

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

        foreach ($item as $graphic) {
            $this->addDataForReferenceCheck($graphic);
        }

        return $this->handleCollection($item);
    }


     private function handleCollection(GraphicLanguageCollection $collection): bool
     {
         $retVal = true;

         foreach ($collection as $langCode => $graphic) {
             $retVal = $retVal && $this->appendItemToOutputFile($langCode, $graphic);
         }

         return $retVal;
     }


    private function addDataForReferenceCheck(IGraphic $item): void
    {
        foreach ($item->getReprintReferences() as $reference) {
            if (!in_array($reference->getInventoryNumber(), $this->inventoryNumberList, true)) {
                $this->objectsWithMissingReferencesList[] = $item->getInventoryNumber();
            }
        }

        foreach ($item->getRelatedWorkReferences() as $reference) {
            if (!in_array($reference->getInventoryNumber(), $this->inventoryNumberList, true)) {
                $this->objectsWithMissingReferencesList[] = $item->getInventoryNumber();
            }
        }
    }


    private function outputReferenceCheckResult(): void
    {
        echo "\n  Graphics with missing references: \n\n";

        if (count($this->objectsWithMissingReferencesList) > 0) {
            foreach ($this->objectsWithMissingReferencesList as $objectInventoryNumber) {
                echo "      - " . $objectInventoryNumber . "\n";
            }
        } else {
            echo "      - No missing references!\n\n";
        }
    }


    /**
     * @return void
     */
    public function done(ProducerInterface $producer)
    {
        $this->closeAllOutputFiles();
        $this->done = true;
        $this->outputFilesByLangCode = [];
        $this->objectsWithMissingReferencesList = [];
        $this->inventoryNumberList = [];

        $this->outputReferenceCheckResult();
    }


    /**
     * @return void
     */
    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }

    private function appendItemToOutputFile(string $langCode, IGraphic $item): bool
    {
        $existenceTypes = $item->getIsVirtual() ? 'virtual' : 'real';
        $metadata = $item->getMetadata();
        $langCode = !is_null($metadata) ? $metadata->getLangCode() : 'unknown';
        $key = $existenceTypes . '.' . $langCode;

        if (!isset($this->outputFilesByLangCode[$key])) {
            $this->outputFilesByLangCode[$key] = [
                "path" => $this->initializeOutputFileForLangCode($key),
                "isFirstItem" => true,
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


    private function initializeOutputFileForLangCode(string $key): string
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
