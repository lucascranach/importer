<?php

namespace CranachDigitalArchive\Importer\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\{ ProducerInterface, ConsumerInterface};
use CranachDigitalArchive\Importer\Traits\Pipeline\ProducerTrait;

/* Describing a hybrid node being producer and consumer in one, usable as base for transformer nodes  */
abstract class Hybrid implements ProducerInterface, ConsumerInterface
{

	use ProducerTrait;

	public function error($error)
	{
		$this->notifyError($error);
	}

	public function done(ProducerInterface $producer)
	{
		$this->notifyDone($producer);
	}

}