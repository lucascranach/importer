<?php

require_once __DIR__ . '/vendor/autoload.php';

use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;
use CranachDigitalArchive\Importer\Pipeline\{PipelineSource, PipelineOperation, PipelineDestination};

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