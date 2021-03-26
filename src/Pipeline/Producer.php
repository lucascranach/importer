<?php

namespace CranachDigitalArchive\Importer\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Pipeline\Traits\ProducerTrait;

abstract class Producer implements ProducerInterface
{
    use ProducerTrait;

    abstract public function run();
}
