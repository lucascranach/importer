<?php

namespace CranachImport\Importers\XML;

require_once 'Language.php';
require_once 'importers/IMultipleFileImporter.php';
require_once 'entities/Painting.php';
require_once 'process/IPipeline.php';
require_once 'importers/inflators/PaintingsXMLInflator.php';

use CranachImport\Language;
use CranachImport\Importers\IMultipleFileImporter;
use CranachImport\Entities\Painting;
use CranachImport\Process\IPipeline;
use CranachImport\Importers\Inflators\PaintingsXMLInflator;


/**
 * Paintings importer on a xml file base
 */
class PaintingsImporter implements IMultipleFileImporter {

	private $sourceFilePaths = [];
	private $items = [];
	private $sourceFilePath = null;
	private $xmlReader = null;
	private $rootElementName = 'CrystalReport';
	private $graphicElementName = 'Group';
	private $pipeline = null;

	function __construct(array $sourceFilePaths) {
		$this->sourceFilePaths = $sourceFilePaths;
	}


	function registerPipeline(IPipeline $pipeline) {
		$this->pipeline = $pipeline;
	}


	function start() {
		$this->checkPipelineBinding();

		/* We have to go through all given files */
		foreach($this->sourceFilePaths as $sourceFilePath) {
			$this->loadNextFile($sourceFilePath);
			$this->checkXMlReaderInitialization();

			echo 'Processing paintings file : ' . $sourceFilePath . "\n";

			/* And process all items in a loaded file */
			while ($this->processNextItem()) {}

			$this->closeXMLReader();
		}

		/* Signaling the pipeline, that we reached the end of the file
			and we are done */
		$this->pipeline->handleDone();
	}


	function loadNextFile(string $sourceFilePath) {
		$this->xmlReader = new \XMLReader();

		if (!$this->xmlReader->open($sourceFilePath)) {
			throw new \Error('Could\'t open paintings xml source file: ' . $sourceFilePath);
		}


		$this->xmlReader->next();

		if ($this->xmlReader->nodeType !== \XMLReader::ELEMENT
			|| $this->xmlReader->name !== $this->rootElementName) {
			throw new \Error('First element is not expected \'' . $this->rootElementName . '\'');
		}

		/* Entering the root node */
		$this->xmlReader->read();
	}


	function closeXMLReader() {
		if (!is_null($this->xmlReader)) {
			$this->xmlReader->close();
			$this->xmlReader = null;
		}
	}


	private function processNextItem() {
		/* Skipping empty text nodes */
		while ($this->xmlReader->next()
			&& $this->xmlReader->nodeType !== \XMLReader::ELEMENT
			&& $this->xmlReader->name !== $this->graphicElementName) {}

		/* Returning if we get to the end of the file */
		if ($this->xmlReader->nodeType === \XMLReader::NONE) {
			return false;
		}

		$this->transformCurrentItem();
		return true;
	}


	private function transformCurrentItem() {
		/* Preparing the painting objects for the different languages */
		$paintingDe = new Painting;
		$paintingDe->setLangCode(Language::DE);

		$paintingEn = new Painting;
		$paintingEn->setLangCode(Language::EN);

		$xmlNode = $this->convertCurrentItemToSimpleXMLElement();

		/* Moved the inflation action(s) into its own class */
		PaintingsXMLInflator::inflate($xmlNode, $paintingDe, $paintingEn);

		/* Passing the painting objects to the pipeline */
		$this->pipeline->handleIncomingItem($paintingDe);
		$this->pipeline->handleIncomingItem($paintingEn);
	}


	private function convertCurrentItemToSimpleXMLElement(): \SimpleXMLElement {
		$element = $this->xmlReader->expand();

		$doc = new \DomDocument();
		$node = $doc->importNode($element, true);
		$doc->appendChild($node);

		return simplexml_import_dom($node, null);
	}


	private function checkXMlReaderInitialization() {
		if (is_null($this->xmlReader)) {
			throw new \Error('Paintings XML-Reader was not correctly initialized!');
		}
	}


	private function checkPipelineBinding() {
		if (is_null($this->pipeline)) {
			throw new \Error('No pipeline bound!');
		}
	}

}