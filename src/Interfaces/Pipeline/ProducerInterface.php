<?php

namespace CranachDigitalArchive\Importer\Interfaces\Pipeline;

interface ProducerInterface extends NodeInterface
{
    public function run();
    public function isReady(): bool;
    public function pipe(ConsumerInterface ...$nodes): ProducerInterface;
    public function getConsumerNodes(): array;
    public function next($data);
    public function isDone(): bool;
    public function notifyError($error);
    public function notifyDone(ProducerInterface $producer);
}
