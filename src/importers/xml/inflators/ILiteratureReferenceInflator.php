<?php

namespace CranachImport\Importers\XML\Inflators;

require_once 'IInflator.php';
require_once 'entities/LiteratureReference.php';

use CranachImport\Entities\LiteratureReference;


/**
 * Interface describing an literature reference inflator
 */
interface ILiteratureReferenceInflator extends IInflator {

	/**
	 * Inflates the passed literature reference object
	 *
	 * @param \SimpleXMLElement &$node Current graphics element node
	 * @param LiteratureReference &$literatureReference LiteratureReference object holding the information
	 */
	static function inflate(\SimpleXMLElement &$node,
	                        LiteratureReference &$literatureReference);

}