<?php

namespace CranachDigitalArchive\Importer\Pipeline;

use CranachDigitalArchive\Importer\Traits\Pipeline\{PipelineChild, PipelineParent, PipelinePullable};
use CranachDigitalArchive\Importer\Interfaces\Pipeline\{IPipelineChild, IPipelineParent, IPipelinePullable};


abstract class PipelineOperation implements IPipelineChild, IPipelineParent, IPipelinePullable {

	use PipelineChild, PipelineParent, PipelinePullable;

}
