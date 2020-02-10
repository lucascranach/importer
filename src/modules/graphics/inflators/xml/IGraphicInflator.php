<?php

namespace CranachImport\Modules\Graphics\Inflators\XML;

require_once 'interfaces/inflators/IInflator.php';
require_once 'modules/graphics/entities/Graphic.php';

use CranachImport\Interfaces\Inflators\IInflator;
use CranachImport\Modules\Graphics\Entities\Graphic;


/**
 * Interface describing a graphic inflator
 */
interface IGraphicInflator extends IInflator {

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