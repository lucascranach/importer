<?php

namespace CranachDigitalArchive\Importer\Pipeline\Traits;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;

trait ConsumerTrait
{
    private $connectedProducers = [];

    /**
     * @return void
     */
    public function registerProducerNode(IProducer $producer)
    {
        $this->connectedProducers[] = $producer;
    }

    /**
     * @return void
     */
    public function unregisterProducerNode(IProducer $producer)
    {
        $this->connectedProducers = array_filter(
            $$this->connectedProducers,
            function ($connectedProducer) use ($producer) {
                return $connectedProducer !== $producer;
            },
        );
    }

    public function getProducerNodes(): array
    {
        return $this->connectedProducers;
    }
}
