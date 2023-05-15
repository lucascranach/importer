<?php

namespace CranachDigitalArchive\Importer\Interfaces\Pipeline;

/* Describing a consumer */
interface IConsumer extends INode
{
    public function registerProducerNode(IProducer $producer);
    public function unregisterProducerNode(IProducer $producer);
    public function getProducerNodes(): array;
    public function handleItem($item): bool;
    public function error($error);
    public function done(IProducer $producer);
}
