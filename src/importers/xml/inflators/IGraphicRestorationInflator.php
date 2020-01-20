<?php

namespace CranachImport\Importers\XML\Inflators;

require_once 'IInflator.php';
require_once 'entities/GraphicRestoration.php';

use CranachImport\Entities\GraphicRestoration;


/**
 * Interface describing a graphic restoration inflator
 */
interface IGraphicRestorationInflator extends IInflator {

	/**
	 * Inflates the passed graphic restoration object
	 *
	 * @param \SimpleXMLElement &$node Current graphics element node
	 * @param GraphicRestoration &$graphicRestoration GraphicRestoration object
	 */
	static function inflate(\SimpleXMLElement &$node,
	                        GraphicRestoration &$graphicRestoration);

}