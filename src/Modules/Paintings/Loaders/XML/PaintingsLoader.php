<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Loaders\XML;

use Error;
use DOMDocument;
use XMLReader;
use SimpleXMLElement;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Loaders\IMultipleFileLoader;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Painting;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\Paintings\Inflators\XML\PaintingInflator;

/**
 * Paintings loader on a xml file base
 */
class PaintingsLoader extends Producer implements IMultipleFileLoader
{
    private $sourceFilePaths = [];
    private $xmlReader = null;
    private $rootElementName = 'CrystalReport';
    private $graphicElementName = 'Group';


    public function __construct()
    {
    }


    public static function withSourcesAt(array $sourceFilePaths)
    {
        $loader = new self;

        $loader->sourceFilePaths = $sourceFilePaths;

        return $loader;
    }


    public function run()
    {
        /* We have to go through all given files */
        foreach ($this->sourceFilePaths as $sourceFilePath) {
            $this->loadNextFile($sourceFilePath);
			$this->checkXMlReaderInitialization();

            echo 'Processing paintings file : ' . $sourceFilePath . "\n";

            /* And process all items in a loaded file */
            while ($this->processNextItem()) {
            }

            $this->closeXMLReader();
        }

        /* Signaling that we are done reading in the xml */
        $this->notifyDone();
    }


    public function loadNextFile(string $sourceFilePath)
    {
        $this->xmlReader = new XMLReader();

        if (!$this->xmlReader->open($sourceFilePath)) {
            throw new Error('Could\'t open paintings xml source file: ' . $sourceFilePath);
        }


        $this->xmlReader->next();

        if ($this->xmlReader->nodeType !== XMLReader::ELEMENT
            || $this->xmlReader->name !== $this->rootElementName) {
            throw new Error('First element is not expected \'' . $this->rootElementName . '\'');
        }

        /* Entering the root node */
        $this->xmlReader->read();
    }


    public function closeXMLReader()
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
            && $this->xmlReader->name !== $this->graphicElementName) {
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
        /* Preparing the painting objects for the different languages */
        $paintingDe = new Painting;
        $paintingDe->setLangCode(Language::DE);

        $paintingEn = new Painting;
        $paintingEn->setLangCode(Language::EN);

        $xmlNode = $this->convertCurrentItemToSimpleXMLElement();

        /* Moved the inflation action(s) into its own class */
        PaintingInflator::inflate($xmlNode, $paintingDe, $paintingEn);

        /* Passing the graphic objects to the next nodes in the pipeline */
        $this->next($paintingDe);
        $this->next($paintingEn);
    }


    private function convertCurrentItemToSimpleXMLElement(): SimpleXMLElement
    {
        $element = $this->xmlReader->expand();

        $doc = new DOMDocument();
        $node = $doc->importNode($element, true);
        $doc->appendChild($node);

        return simplexml_import_dom($node, null);
    }


    private function checkXMlReaderInitialization()
    {
        if (is_null($this->xmlReader)) {
            throw new Error('Paintings XML-Reader was not correctly initialized!');
        }
    }
}
