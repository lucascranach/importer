<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Loaders\XML;

use Error;
use DOMDocument;
use SimpleXMLElement;
use XMLReader;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\Archival;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Loaders\IFileLoader;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\Archivals\Inflators\XML\ArchivalInflator;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;

/**
 * Archivals loader on a xml file base
 */
class ArchivalsLoader extends Producer implements IFileLoader
{
    private $xmlReader = null;
    private $rootElementName = 'CrystalReport';
    private $graphicElementName = 'Group';
    private $sourceFilePath = '';

    private function __construct()
    {
    }

    /**
     * @return self
     */
    public static function withSourceAt(string $sourceFilePath)
    {
        $loader = new self;
        $loader->xmlReader = new XMLReader();
        $loader->sourceFilePath = $sourceFilePath;

        if (!file_exists($sourceFilePath)) {
            throw new Error('Archivals xml source file does not exit: ' . $sourceFilePath);
        }

        return $loader;
    }

    /**
     * @return void
     */
    public function run()
    {
        $this->checkXMlReaderInitialization();

        if (!$this->xmlReader->open($this->sourceFilePath)) {
            throw new Error('Could\'t open archivals xml source file: ' . $this->sourceFilePath);
        }

        echo 'Processing archivals file : ' . $this->sourceFilePath . "\n";

        $this->xmlReader->next();

        if ($this->xmlReader->nodeType !== XMLReader::ELEMENT
            || $this->xmlReader->name !== $this->rootElementName) {
            throw new Error('First element is not expected \'' . $this->rootElementName . '\'');
        }

        /* Entering the root node */
        $this->xmlReader->read();

        while ($this->processNextItem()) {
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
        $metadata = new Metadata;
        $metadata->setEntityType(Archival::ENTITY_TYPE);

        /* Preparing the graphic objects for the different languages */
        $archivalDe = new Archival;
        $metadata->setLangCode(Language::DE);
        $archivalDe->setMetadata($metadata);

        $metadata = clone $metadata;

        $archivalEn = new Archival;
        $metadata->setLangCode(Language::EN);
        $archivalEn->setMetadata($metadata);

        $xmlNode = $this->convertCurrentItemToSimpleXMLElement();

        /* Moved the inflation action(s) into its own class */
        ArchivalInflator::inflate($xmlNode, $archivalDe, $archivalEn);

        /* Passing the archival objects to the next nodes in the pipeline */
        $this->next($archivalDe);
        $this->next($archivalEn);
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
