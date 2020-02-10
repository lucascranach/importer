<?php

namespace CranachImport\Traits\Pipeline;

require_once 'interfaces/pipeline/IPipelinePullable.php';
require_once 'interfaces/pipeline/IPipelineSource.php';

use CranachImport\Interfaces\Pipeline\IPipelinePullable;
use CranachImport\Interfaces\Pipeline\IPipelineSource;


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
