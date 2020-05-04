<?php

namespace CranachDigitalArchive\Importer\Traits\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\{Pipeline\IPipelineParent, Entities\IBaseItem};


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
