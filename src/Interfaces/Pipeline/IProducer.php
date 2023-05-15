<?php

namespace CranachDigitalArchive\Importer\Interfaces\Pipeline;

interface IProducer extends INode
{
    public function run();
    public function isReady(): bool;
    public function pipe(IConsumer ...$nodes): IProducer;
    public function getConsumerNodes(): array;
    public function next($data);
    public function isDone(): bool;
    public function notifyError($error);
    public function notifyDone(IProducer $producer);
}
