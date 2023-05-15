<?php

namespace CranachDigitalArchive\Importer\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Pipeline\Traits\ProducerTrait;

abstract class Producer implements IProducer
{
    use ProducerTrait;

    abstract public function run();
}
