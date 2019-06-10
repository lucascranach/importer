<?php

namespace CranachImport\Importers\Inflators;

require_once 'entities/Graphic.php';

use CranachImport\Entities\Graphic;


interface IInflator {
	static function inflate(\SimpleXMLElement &$node,
	                        Graphic &$graphicDe,
	                        Graphic &$graphicEn);
}