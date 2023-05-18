<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus\Loaders\XML;

use CranachDigitalArchive\Importer\Modules\Thesaurus\Inflators\XML\ThesaurusInflator;
use Error;
use SimpleXMLElement;
use CranachDigitalArchive\Importer\Interfaces\Loaders\IMultipleFileLoader;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\Thesaurus;

/**
 * Paintings loader on a xml file base
 */
class ThesaurusLoader extends Producer implements IMultipleFileLoader
{
    private $sourceFilePaths = [];
    private $rootElementName = 'root';


    private function __construct()
    {
    }


    /**
     * @return self
     */
    public static function withSourcesAt(array $sourceFilePaths)
    {
        $loader = new self;

        $loader->sourceFilePaths = $sourceFilePaths;

        foreach ($sourceFilePaths as $sourceFilePath) {
            if (!file_exists($sourceFilePath)) {
                throw new Error('Thesaurus xml source file does not exit: ' . $sourceFilePath);
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
            echo 'Processing thesaurus file : ' . $sourceFilePath . "\n";

            $thesaurusContent = file_get_contents($sourceFilePath);
            $xmlNode = new SimpleXMLElement($thesaurusContent);
            unset($thesaurusContent);

            if ($xmlNode->getName() !== $this->rootElementName) {
                throw new Error('Unexpected root element in thesaurus reference xml source file!');
            }

            $thesaurus = new Thesaurus;

            ThesaurusInflator::inflate($xmlNode, $thesaurus);

            unset($xmlNode);

            $this->next($thesaurus);
        }

        /* Signaling that we are done reading in the xml */
        $this->notifyDone();
    }
}
