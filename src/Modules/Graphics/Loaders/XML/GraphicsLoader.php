<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML;

use Error;
use DOMDocument;
use SimpleXMLElement;
use XMLReader;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Interfaces\Loaders\IFileLoader;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\Graphics\Inflators\XML\GraphicInflator;

/**
 * Graphics loader on a xml file base
 */
class GraphicsLoader extends Producer implements IFileLoader
{
    private $xmlReader = null;
    private $rootElementName = 'CrystalReport';
    private $graphicElementName = 'Group';
    private $sourceFilePath = '';

    private function __construct()
    {
    }

    public static function withSourceAt(string $sourceFilePath)
    {
        $loader = new self;
        $loader->xmlReader = new XMLReader();
        $loader->sourceFilePath = $sourceFilePath;

        if (!file_exists($sourceFilePath)) {
            throw new Error('Graphics xml source file does not exit: ' . $sourceFilePath);
        }

        return $loader;
    }

    public function run()
    {
        $this->checkXMlReaderInitialization();

        if (!$this->xmlReader->open($this->sourceFilePath)) {
            throw new Error('Could\'t open graphics xml source file: ' . $this->sourceFilePath);
        }

        echo 'Processing graphics file : ' . $this->sourceFilePath . "\n";

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
        /* Preparing the graphic objects for the different languages */
        $graphicDe = new Graphic;
        $graphicDe->setLangCode(Language::DE);

        $graphicEn = new Graphic;
        $graphicEn->setLangCode(Language::EN);

        $xmlNode = $this->convertCurrentItemToSimpleXMLElement();

        /* Moved the inflation action(s) into its own class */
        GraphicInflator::inflate($xmlNode, $graphicDe, $graphicEn);

        /* Passing the graphic objects to the next nodes in the pipeline */
        $this->next($graphicDe);
        $this->next($graphicEn);
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
            throw new Error('Graphics XML-Reader was not correctly initialized!');
        }
    }
}
