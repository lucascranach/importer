<?php

namespace CranachImport\Importers\XML\Inflators;

require_once 'IInflator.php';
require_once 'entities/Painting.php';

use CranachImport\Entities\Painting;


/**
 * Interface describing a painting inflator
 */
interface IPaintingInflator extends IInflator {

	/**
	 * Inflates the passed painting objects
	 *
	 * @param \SimpleXMLElement &$node Current graphics element node
	 * @param Graphic &$paintingDe Painting object holding the german informations
	 * @param Graphic &$paintingEn Painting object holding the english informations
	 */
	static function inflate(\SimpleXMLElement &$node,
	                        Painting &$paintingDe,
	                        Painting &$paintingEn);

}