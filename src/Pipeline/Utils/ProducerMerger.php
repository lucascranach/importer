<?php

namespace CranachDigitalArchive\Importer\Pipeline\Utils;

use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use Error;

/* Pipeline node to merge multiple producer nodes into one */
final class ProducerMerger extends Hybrid
{
    private $producersToForward = [];
    private $doneProducers = [];


    private function __construct()
    {
    }

    public static function merge(IProducer ...$producers): self
    {
        if (count($producers) === 0) {
            throw new Error('No producers passed to ' . get_called_class());
        }

        $nodeMerger = new self;
        $nodeMerger->producersToForward = $producers;

        foreach ($producers as $producer) {
            $producer->pipeline($nodeMerger);
        }

        return $nodeMerger;
    }


    public function handleItem($item): bool
    {
        $this->next($item);

        return true;
    }

    /**
     * @return void
     */
    public function done(IProducer $producer)
    {
        if (!in_array($producer, $this->producersToForward, true)) {
            throw new Error('Unexpected producer passed for done trigger: ' . get_class($producer));
        }

        if (!in_array($producer, $this->doneProducers, true)) {
            $this->doneProducers[] = $producer;
        }

        if (count($this->doneProducers) === count($this->producersToForward)) {
            $this->notifyDone($this);
        }
    }
}
