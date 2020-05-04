<?php

namespace CranachDigitalArchive\Importer\Interfaces\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;


interface IPipelineChild {

	function setParent(IPipelineParent $parent);
	function getParent(): ?IPipelineParent;
	function handleItem(IBaseItem $item);

}
