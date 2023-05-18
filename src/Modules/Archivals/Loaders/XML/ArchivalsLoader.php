<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Loaders\XML;

use Error;
use DOMDocument;
use SimpleXMLElement;
use XMLReader;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\Archival;
use CranachDigitalArchive\Importer\Interfaces\Loaders\IMultipleFileLoader;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\ArchivalLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\Archivals\Inflators\XML\ArchivalInflator;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;

/**
 * Archivals loader on a xml file base
 */
class ArchivalsLoader extends Producer implements IMultipleFileLoader
{
    private $xmlReader = null;
    private $rootElementName = 'CrystalReport';
    private $graphicElementName = 'Group';
    private $sourceFilePaths = [];

    private function __construct()
    {
    }

    /**
     * @return self
     */
    public static function withSourcesAt(array $sourceFilePaths)
    {
        $loader = new self;
        $loader->xmlReader = new XMLReader();
        $loader->sourceFilePaths = $sourceFilePaths;

        foreach ($sourceFilePaths as $sourceFilePath) {
            if (!file_exists($sourceFilePath)) {
                throw new Error('Archivals xml source file does not exit: ' . $sourceFilePath);
            }
        }

        return $loader;
    }

    /**
     * @return void
     */
    public function run()
    {
        /* We have to go through all given files */
        foreach ($this->sourceFilePaths as $sourceFilePath) {
            $this->checkXMlReaderInitialization();

            if (!$this->xmlReader->open($sourceFilePath)) {
                throw new Error('Could\'t open archivals xml source file: ' . $sourceFilePath);
            }

            echo 'Processing archivals file : ' . $sourceFilePath . "\n";

            $this->xmlReader->next();

            if ($this->xmlReader->nodeType !== XMLReader::ELEMENT
                || $this->xmlReader->name !== $this->rootElementName) {
                throw new Error('First element is not expected \'' . $this->rootElementName . '\'');
            }

            /* Entering the root node */
            $this->xmlReader->read();

            while ($this->processNextItem()) {
            }
        }

        /* Signaling that we are done reading in the xml */
        $this->notifyDone();
    }


    private function processNextItem(): bool
    {
        /* Skipping empty text nodes */
        while ($this->xmlReader->next()
            && $this->xmlReader->nodeType !== XMLReader::ELEMENT
            && $this->xmlReader->name !== $this->graphicElementName) {
        }

        /* Returning if we get to the end of the file */
        if ($this->xmlReader->nodeType === XMLReader::NONE) {
            return false;
        }

        $this->transformCurrentItem();
        return true;
    }


    private function transformCurrentItem(): void
    {
        $archivalCollection = ArchivalLanguageCollection::create();

        foreach ($archivalCollection as $langCode => $archival) {
            $metadata = new Metadata();
            $metadata->setEntityType(Archival::ENTITY_TYPE);
            $metadata->setLangCode($langCode);

            $archival->setMetadata($metadata);
        }

        $xmlNode = $this->convertCurrentItemToSimpleXMLElement();

        /* Moved the inflation action(s) into its own class */
        ArchivalInflator::inflate($xmlNode, $archivalCollection);

        /* Passing the archival objects to the next nodes in the pipeline */
        $this->next($archivalCollection);
    }


    private function convertCurrentItemToSimpleXMLElement(): SimpleXMLElement
    {
        $element = $this->xmlReader->expand();

        $doc = new DOMDocument();
        $node = $doc->importNode($element, true);
        $doc->appendChild($node);

        return simplexml_import_dom($node);
    }


    private function checkXMlReaderInitialization(): void
    {
        if (is_null($this->xmlReader)) {
            throw new Error('Archivals XML-Reader was not correctly initialized!');
        }
    }
}
