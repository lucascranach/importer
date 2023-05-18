<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML;

use Error;
use DOMDocument;
use SimpleXMLElement;
use XMLReader;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Interfaces\Loaders\IMultipleFileLoader;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\Graphics\Inflators\XML\GraphicInflator;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;

/**
 * Graphics loader on a xml file base
 */
class GraphicsLoader extends Producer implements IMultipleFileLoader
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
                throw new Error('Graphics xml source file does not exit: ' . $sourceFilePath);
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
                throw new Error('Could\'t open graphics xml source file: ' . $sourceFilePath);
            }

            echo 'Processing graphics file : ' . $sourceFilePath . "\n";

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
        $graphicCollection = GraphicLanguageCollection::create();

        foreach ($graphicCollection as $langCode => $graphic) {
            $metadata = new Metadata;
            $metadata->setEntityType(Graphic::ENTITY_TYPE);
            $metadata->setLangCode($langCode);

            $graphic->setMetadata($metadata);
        }

        $xmlNode = $this->convertCurrentItemToSimpleXMLElement();

        /* Moved the inflation action(s) into its own class */
        GraphicInflator::inflate($xmlNode, $graphicCollection);

        /* Passing the graphic collection to the next nodes in the pipeline */
        $this->next($graphicCollection);
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
            throw new Error('Graphics XML-Reader was not correctly initialized!');
        }
    }
}
