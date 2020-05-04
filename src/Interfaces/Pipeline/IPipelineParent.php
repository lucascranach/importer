<?php

namespace CranachDigitalArchive\Importer\Interfaces\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;


interface IPipelineParent {

	function getChildren(): array;
	function pipe(IPipelineChild ...$children): ?IPipelineChild;
	function propagate(IBaseItem $item);
	function done();

}
