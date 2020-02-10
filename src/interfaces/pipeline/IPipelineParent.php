<?php

namespace CranachImport\Interfaces\Pipeline;

require_once 'IPipelineChild.php';
require_once 'interfaces/entities/IBaseItem.php';

use CranachImport\Interfaces\Entities\IBaseItem;


interface IPipelineParent {

	function getChildren(): array;
	function pipe(IPipelineChild ...$children): ?IPipelineChild;
	function propagate(IBaseItem $item);
	function done();

}
