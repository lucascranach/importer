<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus\Loaders\XML;

use CranachDigitalArchive\Importer\Modules\Thesaurus\Inflators\XML\ThesaurusInflator;
use Error;
use SimpleXMLElement;
use CranachDigitalArchive\Importer\Interfaces\Loaders\IFileLoader;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\Thesaurus;

/**
 * Paintings loader on a xml file base
 */
class ThesaurusLoader extends Producer implements IFileLoader
{
	private $sourceFilePath = '';
	private $rootElementName = 'root';


	private function __construct()
	{
	}


	public static function withSourceAt(string $sourceFilePath)
	{
		$loader = new self;

		$loader->sourceFilePath = $sourceFilePath;

		if (!file_exists($sourceFilePath)) {
			throw new Error('Thesaurus reference xml source file does not exit: ' . $sourceFilePath);
		}

		return $loader;
	}


	public function run()
	{
		echo 'Processing thesaurus file : ' . $this->sourceFilePath . "\n";

		$thesaurusContent = file_get_contents($this->sourceFilePath);
		$xmlNode = new SimpleXMLElement($thesaurusContent);
		unset($thesaurusContent);

		if ($xmlNode->getName() !== $this->rootElementName) {
			throw new Error('Unexpected root element in thesaurus reference xml source file!');
		}

		$thesaurus = new Thesaurus;

		ThesaurusInflator::inflate($xmlNode, $thesaurus);

		unset($xmlElement);

		$this->next($thesaurus);

		/* Signaling that we are done reading in the xml */
		$this->notifyDone();
	}
}
