<?php

namespace CranachDigitalArchive\Importer\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ConsumerInterface;
use CranachDigitalArchive\Importer\Traits\Pipeline\ProducerTrait;
use CranachDigitalArchive\Importer\Traits\Pipeline\ConsumerTrait;

/* Describing a hybrid node being producer and consumer in one, usable as base for transformer nodes  */
abstract class Hybrid implements ProducerInterface, ConsumerInterface
{
    use ProducerTrait;
    use ConsumerTrait;


    public function error($error)
    {
        $this->notifyError($error);
    }


    public function done(ProducerInterface $producer)
    {
        $this->notifyDone($producer);
    }
}
