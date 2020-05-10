<?php

namespace CranachDigitalArchive\Importer\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\{ConsumerInterface, ProducerInterface};


abstract class Consumer implements ConsumerInterface
{

	abstract public function handleItem($item): bool;
	abstract public function error($error);
	abstract public function done(ProducerInterface $producer);

}
