<?php

namespace CranachDigitalArchive\Importer\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\{ConsumerInterface, ProducerInterface};
use CranachDigitalArchive\Importer\Traits\Pipeline\ConsumerTrait;


abstract class Consumer implements ConsumerInterface
{

	use ConsumerTrait;

	abstract public function handleItem($item): bool;
	abstract public function error($error);
	abstract public function done(ProducerInterface $producer);

}
