<?php

namespace CranachImport\Interfaces\Pipeline;

require_once 'IPipelineParent.php';
require_once 'interfaces/entities/IBaseItem.php';

use CranachImport\Interfaces\Entities\IBaseItem;


interface IPipelineChild {

	function setParent(IPipelineParent $parent);
	function getParent(): ?IPipelineParent;
	function handleItem(IBaseItem $item);

}
