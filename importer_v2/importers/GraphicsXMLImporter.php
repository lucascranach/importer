<?php

namespace CranachImport\Importers;

require_once 'Language.php';
require_once 'IFileImporter.php';
require_once 'entities/IBaseItem.php';
require_once 'entities/Graphic.php';
require_once 'process/IPipeline.php';
require_once 'importers/inflators/GraphicsXMLInflator.php';

use CranachImport\Language;
use CranachImport\Entities\IBaseItem;
use CranachImport\Entities\Graphic;
use CranachImport\Process\IPipeline;
use CranachImport\Importers\Inflators\GraphicsXMLInflator;


/**
 * Graphics importer on a xml file base
 */
class GraphicsXMLImporter implements IFileImporter {

	private $items = [];
	private $sourceFilePath = null;
	private $xmlReader = null;
	private $rootElementName = 'CrystalReport';
	private $graphicElementName = 'Group';
	private $pipeline = null;

	function __construct(string $sourceFilePath) {
		$this->xmlReader = new \XMLReader();

		if (!$this->xmlReader->open($sourceFilePath)) {
			throw new \Error('Could\'t open graphics xml source file: ' . $sourceFilePath);
		}


		$this->xmlReader->next();

		if ($this->xmlReader->nodeType !== \XMLReader::ELEMENT
			|| $this->xmlReader->name !== $this->rootElementName) {
			throw new \Error('First element is not expected \'' . $this->rootElementName . '\'');
		}

		/* Entering the root node */
		$this->xmlReader->read();
	}


	function registerPipeline(IPipeline $pipeline) {
		$this->pipeline = $pipeline;
	}


	function start() {
		$this->checkXMlReaderInitialization();
		$this->checkPipelineBinding();

		while ($this->processNextItem()) {}

		/* Signaling the pipeline, that we reached the end of the file
			and we are done */
		$this->pipeline->handleDone();
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
		/* Preparing the graphic objects for the different languages */
		$graphicDe = new Graphic;
		$graphicDe->setLangCode(Language::DE);

		$graphicEn = new Graphic;
		$graphicEn->setLangCode(Language::EN);

		$xmlNode = $this->convertCurrentItemToSimpleXMLElement();

		/* Moved the inflation action(s) into its own class */
		GraphicsXMLInflator::inflate($xmlNode, $graphicDe, $graphicEn);

		/* Passing the graphic objects to the pipeline */
		$this->pipeline->handleIncomingItem($graphicDe);
		$this->pipeline->handleIncomingItem($graphicEn);
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
			throw new \Error('Graphics XML-Reader was not correctly initialized!');
		}
	}


	private function checkPipelineBinding() {
		if (is_null($this->pipeline)) {
			throw new \Error('No pipeline bound!');
		}
	}

}