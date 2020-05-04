<?php

namespace CranachDigitalArchive\Importer\Traits\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\{IPipelinePullable, IPipelineSource};


trait PipelinePullable {

	function pull() {

		$parent = $this->getParent();

		if (is_null($parent)) {
			throw new \Exception('Can not pull without parent');
		}

		if (is_subclass_of($parent, IPipelinePullable::class)) {
			$parent->pull();
		}

		if (is_subclass_of($parent, IPipelineSource::class)) {
			$parent->start();
		}
	
	}

}
