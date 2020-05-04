<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Inflators\XML;

use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;


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