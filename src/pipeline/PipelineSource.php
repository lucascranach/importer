<?php

namespace CranachImport\Pipeline;

require_once 'traits/pipeline/PipelineParent.php';
require_once 'interfaces/pipeline/IPipelineParent.php';
require_once 'interfaces/pipeline/IPipelineSource.php';

use CranachImport\Traits\Pipeline\PipelineParent;
use CranachImport\Traits\Pipeline\PipelineStart;
use CranachImport\Interfaces\Pipeline\IPipelineParent;
use CranachImport\Interfaces\Pipeline\IPipelineSource;


abstract class PipelineSource implements IPipelineParent, IPipelineSource {

	use PipelineParent;

	abstract function start();

}
