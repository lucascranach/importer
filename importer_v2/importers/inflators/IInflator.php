<?php

namespace CranachImport\Importers\Inflators;

require_once 'entities/Graphic.php';

use CranachImport\Entities\Graphic;


/**
 * Interface describing an inflator
 */
interface IInflator {

	/**
	 * Inflates the passed graphic objects
	 *
	 * @param \SimpleXMLElement &$node Current graphics element node
	 * @param Graphic &$graphicDe Graphic object holding the german informations
	 * @param Graphic &$graphicEn Graphic object holding the english informations
	 */
	static function inflate(\SimpleXMLElement &$node,
	                        Graphic &$graphicDe,
	                        Graphic &$graphicEn);

}