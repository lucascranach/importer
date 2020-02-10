<?php

namespace CranachImport\Traits\Pipeline;

require_once 'interfaces/pipeline/IPipelineParent.php';
require_once 'interfaces/pipeline/IPipelineChild.php';
require_once 'interfaces/entities/IBaseItem.php';

use CranachImport\Interfaces\Pipeline\IPipelineParent;
use CranachImport\Interfaces\Pipeline\IPipelineChild;
use CranachImport\Interfaces\Entities\IBaseItem;


trait PipelineChild {

	private $parent = null;


	function setParent(IPipelineParent $parent) {
		$this->parent = $parent;
	}

	function getParent(): ?IPipelineParent {
		return $this->parent;
	}

	abstract function handleItem(IBaseItem $item);

}
