<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Loaders\XML;

use Error;
use DOMDocument;
use SimpleXMLElement;
use XMLReader;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReference;
use CranachDigitalArchive\Importer\Interfaces\Loaders\IMultipleFileLoader;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Inflators\XML\LiteratureReferencesInflator;

/**
 * LitereatureReferences loader on a xml file base
 */
class LiteratureReferencesLoader extends Producer implements IMultipleFileLoader
{
    private $xmlReader = null;
    private $rootElementName = 'CrystalReport';
    private $literatureReferenceElementName = 'Group';
    private $sourceFilePaths = [];

    public function __construct()
    {
    }


    public static function withSourcesAt(array $sourceFilePaths)
    {
        $loader = new self;
        $loader->xmlReader = new XMLReader();
        $loader->sourceFilePaths = $sourceFilePaths;

        foreach ($sourceFilePaths as $sourceFilePath) {
            if (!file_exists($sourceFilePath)) {
                throw new Error('LiteratureReferences xml source file does not exit: ' . $sourceFilePath);
            }
        }

        return $loader;
    }


    public function run()
    {
        /* We have to go through all given files */
        foreach ($this->sourceFilePaths as $sourceFilePath) {
            $this->loadNextFile($sourceFilePath);
            $this->checkXMlReaderInitialization();

            echo 'Processing literature references file : ' . $sourceFilePath . "\n";

            /* And process all items in a loaded file */
            while ($this->processNextItem()) {
            }

            $this->closeXMLReader();
        }

        /* Signaling that we are done reading in the xml */
        $this->notifyDone();
    }

    private function loadNextFile(string $sourceFilePath)
    {
        $this->xmlReader = new XMLReader();

        if (!$this->xmlReader->open($sourceFilePath)) {
            throw new Error('Could\'t open literatureReferences xml source file: ' . $sourceFilePath);
        }


        $this->xmlReader->next();

        if ($this->xmlReader->nodeType !== XMLReader::ELEMENT
            || $this->xmlReader->name !== $this->rootElementName) {
            throw new Error('First element is not expected \'' . $this->rootElementName . '\'');
        }

        /* Entering the root node */
        $this->xmlReader->read();
    }

    private function closeXMLReader()
    {
        if (!is_null($this->xmlReader)) {
            $this->xmlReader->close();
            $this->xmlReader = null;
        }
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
