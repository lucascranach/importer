<?php

namespace CranachDigitalArchive\Importer\Pipeline;

use CranachDigitalArchive\Importer\Traits\Pipeline\{PipelineChild, PipelinePullable};
use CranachDigitalArchive\Importer\Interfaces\Pipeline\{IPipelineChild, IPipelinePullable, IPipelineDestination};


abstract class PipelineDestination implements IPipelineChild, IPipelinePullable, IPipelineDestination {

	use PipelineChild, PipelinePullable;

	abstract function done();

}
