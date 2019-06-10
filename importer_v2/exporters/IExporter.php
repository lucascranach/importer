<?php

namespace CranachImport\Exporters;

require_once 'entities/IBaseItem.php';

use CranachImport\Entities\IBaseItem;


interface IExporter {

	function pushItem(IBaseItem $item);

	function isDone(): bool;

	function done();

}