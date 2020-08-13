<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Loaders\XML;

use Error;
use DOMDocument;
use SimpleXMLElement;
use XMLReader;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReference;
use CranachDigitalArchive\Importer\Interfaces\Loaders\IFileLoader;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Inflators\XML\LiteratureReferencesInflator;

/**
 * LitereatureReferences loader on a xml file base
 */
class LiteratureReferencesLoader extends Producer implements IFileLoader
{
    private $xmlReader = null;
    private $rootElementName = 'CrystalReport';
    private $literatureReferenceElementName = 'Group';
    private $sourceFilePath = '';

    public function __construct()
    {
    }


    public static function withSourceAt(string $sourceFilePath)
    {
        $loader = new self;
        $loader->xmlReader = new XMLReader();
        $loader->sourceFilePath = $sourceFilePath;

        if (!file_exists($sourceFilePath)) {
            throw new Error('LiteratureReferences xml source file does not exit: ' . $sourceFilePath);
        }

        return $loader;
    }


    public function run()
    {
        $this->checkXMlReaderInitialization();

        if (!$this->xmlReader->open($this->sourceFilePath)) {
            throw new Error('Could\'t open literature reference xml source file: ' . $this->sourceFilePath);
        }

        echo 'Processing literature references file : ' . $this->sourceFilePath . "\n";

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


    private function processNextItem()
    {
        /* Skipping empty text nodes */
        while ($this->xmlReader->next()
            && $this->xmlReader->nodeType !== XMLReader::ELEMENT
            && $this->xmlReader->name !== $this->literatureReferenceElementName) {
        }

        /* Returning if we get to the end of the file */
        if ($this->xmlReader->nodeType === XMLReader::NONE) {
            return false;
        }

        $this->transformCurrentItem();
        return true;
    }


    private function transformCurrentItem()
    {
        $literatureReference = new LiteratureReference;

        $xmlNode = $this->convertCurrentItemToSimpleXMLElement();

        /* Moved the inflation action(s) into its own class */
        LiteratureReferencesInflator::inflate($xmlNode, $literatureReference);

        /* Passing the literature reference object to the next nodes in the pipeline */
        $this->next($literatureReference);
    }


    private function convertCurrentItemToSimpleXMLElement(): SimpleXMLElement
    {
        $element = $this->xmlReader->expand();

        $doc = new DomDocument();
        $node = $doc->importNode($element, true);
        $doc->appendChild($node);

        return simplexml_import_dom($node);
    }


    private function checkXMlReaderInitialization()
    {
        if (is_null($this->xmlReader)) {
            throw new Error('LiteratureReference XML-Reader was not correctly initialized!');
        }
    }
}
