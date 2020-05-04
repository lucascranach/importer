<?php

namespace CranachDigitalArchive\Importer\Pipeline;

use CranachDigitalArchive\Importer\Traits\Pipeline\{PipelineParent, PipelineStart};
use CranachDigitalArchive\Importer\Interfaces\Pipeline\{IPipelineParent, IPipelineSource};


abstract class PipelineSource implements IPipelineParent, IPipelineSource {

	use PipelineParent;

	abstract function start();

}
