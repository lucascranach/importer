<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Loaders\XML;

use Error;
use DOMDocument;
use XMLReader;
use SimpleXMLElement;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Interfaces\Loaders\IMultipleFileLoader;

use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Restoration;
use CranachDigitalArchive\Importer\Modules\Restorations\Inflators\XML\RestorationInflator;

/**
 * Restoration loader on a xml file base
 */
class RestorationsLoader extends Producer implements IMultipleFileLoader
{
    private $sourceFilePaths = [];
    private $xmlReader = null;
    private $rootElementName = 'CrystalReport';
    private $restorationElementName = 'Group';


    public function __construct()
    {
    }


    /**
     * @return self
     */
    public static function withSourcesAt(array $sourceFilePaths)
    {
        $loader = new self();
        $loader->xmlReader = new XMLReader();
        $loader->sourceFilePaths = $sourceFilePaths;

        foreach ($sourceFilePaths as $sourceFilePath) {
            if (!file_exists($sourceFilePath)) {
                throw new Error('Restoration xml source file does not exit: ' . $sourceFilePath);
            }
        }

        return $loader;
    }


    /**
     * @return void
     */
    public function run()
    {
        $this->checkXMlReaderInitialization();

        /* We have to go through all given files */
        foreach ($this->sourceFilePaths as $sourceFilePath) {
            $this->loadNextFile($sourceFilePath);
            $this->checkXMlReaderInitialization();

            echo 'Processing restorations file : ' . $sourceFilePath . "\n";

            /* And process all items in a loaded file */
            while ($this->processNextItem()) {
            }

            $this->closeXMLReader();
        }

        /* Signaling that we are done reading in the xml */
        $this->notifyDone();
    }


    public function loadNextFile(string $sourceFilePath): void
    {
        $this->xmlReader = new XMLReader();

        if (!$this->xmlReader->open($sourceFilePath)) {
            throw new Error('Could\'t open restorations xml source file: ' . $sourceFilePath);
        }


        $this->xmlReader->next();

        if ($this->xmlReader->nodeType !== XMLReader::ELEMENT
            || $this->xmlReader->name !== $this->rootElementName) {
            throw new Error('First element is not expected \'' . $this->rootElementName . '\'');
        }

        /* Entering the root node */
        $this->xmlReader->read();
    }


    public function closeXMLReader(): void
    {
        if (!is_null($this->xmlReader)) {
            $this->xmlReader->close();
            $this->xmlReader = null;
        }
    }


    private function processNextItem(): bool
    {
        /* Skipping empty text nodes */
        while ($this->xmlReader->next()
            && $this->xmlReader->nodeType !== XMLReader::ELEMENT
            && $this->xmlReader->name !== $this->restorationElementName) {
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
        /* Preparing the restoration objects for the different languages */
        $restorationDe = new Restoration;
        $restorationDe->setLangCode(Language::DE);

        $restorationEn = new Restoration;
        $restorationEn->setLangCode(Language::EN);

        $xmlNode = $this->convertCurrentItemToSimpleXMLElement();

        /* Moved the inflation action(s) into its own class(es) */
        RestorationInflator::inflate($xmlNode, $restorationDe, $restorationEn);

        /* Passing the restoration objects to the pipeline */
        $this->next($restorationDe);
        $this->next($restorationEn);
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
            throw new Error('Restorations XML-Reader was not correctly initialized!');
        }
    }
}
