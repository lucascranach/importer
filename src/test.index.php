<?php

require_once 'modules/graphics/entities/Graphic.php';

require_once 'interfaces/entities/IBaseItem.php';
require_once 'pipeline/PipelineSource.php';
require_once 'pipeline/PipelineOperation.php';
require_once 'pipeline/PipelineDestination.php';

use CranachImport\Modules\Graphics\Entities\Graphic;

use CranachImport\Interfaces\Entities\IBaseItem;
use CranachImport\Pipeline\PipelineSource;
use CranachImport\Pipeline\PipelineOperation;
use CranachImport\Pipeline\PipelineDestination;

/*
	@TODO: Introduce Pipeline-Class to add Input, Ops and Destinations to; process can only be started through the pipeline
	@TODO: Use src and sink as Input- and Output-Fields?
*/


class Source extends PipelineSource {

	function start() {
		echo "start!\n";
		$g = new Graphic();
		$this->propagate($g);

		$this->done();
	}

}

class Operation extends PipelineOperation {

	function handleItem(IBaseItem $item) {
		$this->propagate($item);
	}

}


class Destination extends PipelineDestination {

	function handleItem(IBaseItem $item) {
		echo "finish!\n";
	}

	function done() {
		echo "DONE!\n";
	}
}



$s = new Source();
$o = new Operation();
$o2 = new Operation();
$o3 = new Operation();
$o4 = new Operation();
$o5 = new Operation();
$d = new Destination();
$d2 = new Destination();

$tmp = $s->pipe(
	$o,
	$o2,
	$o3->pipe($o4, $o5),
);

$tmp->pipe(
	$d,
);

$tmp->pipe(
	$d2,
);

$tmp->pull();