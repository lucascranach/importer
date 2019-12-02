<?php

namespace CranachImport\Importers\Inflators;

require_once 'IPaintingInflator.php';
require_once 'entities/Painting.php';


use CranachImport\Entities\Painting;


/**
 * Paintingss inflator used to inflate german and english painting instances
 * 	by traversing the xml element node and extracting the data in a structured way
 */
class PaintingsXMLInflator implements IPaintingInflator {

	private static $nsPrefix = 'ns';
	private static $ns = 'urn:crystal-reports:schemas:report-detail';
	private static $langSplitChar = '#';

	private function __construct() {}


	public static function inflate(\SimpleXMLElement &$node,
	                               Painting &$paintingDe,
	                               Painting &$paintingEn) {
		$subNode = $node->GroupHeader;

		self::registerXPathNamespace($subNode);
	}


	private static function registerXPathNamespace(\SimpleXMLElement $node) {
		$node->registerXPathNamespace(self::$nsPrefix, self::$ns);
	}


	private static function findElementsByXPath(\SimpleXMLElement $node, string $path) {
		self::registerXPathNamespace($node);

		$splitPath = explode('/', $path);

		$nsPrefix = self::$nsPrefix;
		$xpathStr = './/' . implode('/', array_map(
			function($val) use($nsPrefix) {
				return empty($val) ? $val : $nsPrefix . ':' . $val;
			},
			$splitPath
		));

		return $node->xpath($xpathStr);
	}


	private static function findElementByXPath(\SimpleXMLElement $node, string $path) {
		$result = self::findElementsByXPath($node, $path);

		if (is_array($result) && count($result) > 0) {
			return $result[0];
		}

		return FALSE;
	}


	private static function splitLanguageString(string $langStr): array {
		return array_map('trim', explode(self::$langSplitChar, $langStr));
	}

}