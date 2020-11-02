<?php

namespace CranachDigitalArchive\Importer\Traits\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;

trait ConsumerTrait
{
    private $connectedProducers = [];

    /**
     * @return void
     */
    public function registerProducerNode(ProducerInterface $producer)
    {
        $this->connectedProducers[] = $producer;
    }

    /**
     * @return void
     */
    public function unregisterProducerNode(ProducerInterface $producer)
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
