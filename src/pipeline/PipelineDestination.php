<?php

namespace CranachImport\Pipeline;

require_once 'traits/pipeline/PipelineChild.php';
require_once 'traits/pipeline/PipelinePullable.php';
require_once 'interfaces/pipeline/IPipelineChild.php';
require_once 'interfaces/pipeline/IPipelinePullable.php';
require_once 'interfaces/pipeline/IPipelineDestination.php';

use CranachImport\Traits\Pipeline\PipelineChild;
use CranachImport\Traits\Pipeline\PipelinePullable;
use CranachImport\Interfaces\Pipeline\IPipelineChild;
use CranachImport\Interfaces\Pipeline\IPipelinePullable;
use CranachImport\Interfaces\Pipeline\IPipelineDestination;


abstract class PipelineDestination implements IPipelineChild, IPipelinePullable, IPipelineDestination {

	use PipelineChild, PipelinePullable;

	abstract function done();

}
