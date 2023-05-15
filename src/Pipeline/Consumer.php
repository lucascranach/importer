<?php

namespace CranachDigitalArchive\Importer\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\IConsumer;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Pipeline\Traits\ConsumerTrait;

abstract class Consumer implements IConsumer
{
    use ConsumerTrait;

    abstract public function handleItem($item): bool;
    abstract public function error($error);
    abstract public function done(IProducer $producer);
}
