<?php

namespace CranachDigitalArchive\Importer\Modules\GraphicRestorations\Loaders\XML;

use Error;
use DomDocument;
use XMLReader;
use SimpleXMLElement;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Interfaces\Loaders\IFileLoader;
use CranachDigitalArchive\Importer\Modules\GraphicRestorations\Entities\GraphicRestoration;
use CranachDigitalArchive\Importer\Modules\GraphicRestorations\Inflators\XML\GraphicRestorationsInflator;


/**
 * GraphicsRestoration job on a xml file base
 */
class GraphicRestorationsLoader extends Producer implements IFileLoader {

	private $xmlReader = null;
	private $rootElementName = 'CrystalReport';
	private $graphicElementName = 'Group';

	function __construct()
	{
	}


	static function withSourceAt(string $sourceFilePath)
	{
		$loader = new self();
		$loader->xmlReader = new XMLReader();

		if (!$loader->xmlReader->open($sourceFilePath)) {
			throw new Error('Could\'t open graphics xml source file: ' . $sourceFilePath);
		}

		echo 'Processing graphics restoration file : ' . $sourceFilePath . "\n";


		$loader->xmlReader->next();

		if ($loader->xmlReader->nodeType !== XMLReader::ELEMENT
			|| $loader->xmlReader->name !== $loader->rootElementName) {
			throw new Error('First element is not expected \'' . $loader->rootElementName . '\'');
		}

		/* Entering the root node */
		$loader->xmlReader->read();

		return $loader;
	}


	public function run()
	{
		$this->checkXMlReaderInitialization();

		while ($this->processNextItem()) {}

		/* Signaling that we are done reading in the xml */
		$this->notifyDone();
	}


	private function processNextItem()
	{
		/* Skipping empty text nodes */
		while ($this->xmlReader->next()
			&& $this->xmlReader->nodeType !== XMLReader::ELEMENT
			&& $this->xmlReader->name !== $this->graphicElementName) {}

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
		$graphicRestoration = new GraphicRestoration();

		$xmlNode = $this->convertCurrentItemToSimpleXMLElement();

		/* Moved the inflation action(s) into its own class */
		GraphicRestorationsInflator::inflate($xmlNode, $graphicRestoration);

		/* Passing the graphic objects to the pipeline */
		$this->next($graphicRestoration);
	}


	private function convertCurrentItemToSimpleXMLElement(): SimpleXMLElement
	{
		$element = $this->xmlReader->expand();

		$doc = new DomDocument();
		$node = $doc->importNode($element, true);
		$doc->appendChild($node);

		return simplexml_import_dom($node, null);
	}


	private function checkXMlReaderInitialization() {
		if (is_null($this->xmlReader)) {
			throw new Error('Graphics XML-Reader was not correctly initialized!');
		}
	}

}