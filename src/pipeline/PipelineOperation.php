<?php

namespace CranachImport\Pipeline;

require_once 'traits/pipeline/PipelineChild.php';
require_once 'traits/pipeline/PipelineParent.php';
require_once 'traits/pipeline/PipelinePullable.php';
require_once 'interfaces/pipeline/IPipelineChild.php';
require_once 'interfaces/pipeline/IPipelineParent.php';
require_once 'interfaces/pipeline/IPipelinePullable.php';

use CranachImport\Traits\Pipeline\PipelineChild;
use CranachImport\Traits\Pipeline\PipelineParent;
use CranachImport\Traits\Pipeline\PipelinePullable;
use CranachImport\Interfaces\Pipeline\IPipelineChild;
use CranachImport\Interfaces\Pipeline\IPipelineParent;
use CranachImport\Interfaces\Pipeline\IPipelinePullable;


abstract class PipelineOperation implements IPipelineChild, IPipelineParent, IPipelinePullable {

	use PipelineChild, PipelineParent, PipelinePullable;

}
