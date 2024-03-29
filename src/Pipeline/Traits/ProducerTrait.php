<?php

namespace CranachDigitalArchive\Importer\Pipeline\Traits;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\INode;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IConsumer;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use Error;

trait ProducerTrait
{
    private $consumerNodes = [];
    private $done = false;

    public function isReady(): bool
    {
        return true;
    }

    public function pipeline(IProducer|IConsumer|null ...$nodes): IProducer
    {
        $nodes = array_filter($nodes);

        if (count($nodes) === 0) {
            throw new Error('At least one node expected to build up the pipeline of consumers');
        }

        $this->consumerNodes[] = $nodes[0];

        /* Creating the pipeline by piping each node into each following node */
        array_reduce($nodes, function ($acc, $currentNode) {
            if (is_null($acc)) {
                return $currentNode;
            }

            $acc->pipeline($currentNode);

            return $currentNode;
        }, null);

        return $this;
    }


    /**
     * @return void
     */
    public function next($data)
    {
        foreach ($this->consumerNodes as $consumerNode) {
            $consumerNode->handleItem($data);
        }
    }


    public function getConsumerNodes(): array
    {
        return $this->consumerNodes;
    }


    public function isDone(): bool
    {
        return $this->done;
    }


    /**
     * @return void
     */
    public function notifyError($error)
    {
        foreach ($this->consumerNodes as $consumerNode) {
            $consumerNode->error($error);
        }
    }


    /**
     * @return void
     */
    public function notifyDone(IProducer $producer = null)
    {
        $this->done = true;
        $srcProducer = !is_null($producer) ? $producer : $this;

        foreach ($this->consumerNodes as $consumerNode) {
            $consumerNode->done($srcProducer);
        }
    }


    public function checkForCyclicChains(INode ...$nodes): void
    {
        /* Check for duplicate nodes and prevent cyclic pipe-chains */
        foreach ($nodes as $node) {
            $positions = array_keys($nodes, $node, true);

            if (count($positions) > 1) {
                $duplicatePositions = array_slice($positions, 1);
                $duplicatePositionsStr = implode(', ', $duplicatePositions);

                throw new Error(
                    'Duplicate nodes found for ' . get_class($node) . ' at position/s ' . $duplicatePositionsStr,
                );
            }
        }
    }

    public function checkForExistingConnection(INode ...$nodes): void
    {
        $alreadyConnectedTo = array_search(
            $nodes[0],
            $this->consumerNodes,
            true,
        ) !== false;

        if ($alreadyConnectedTo) {
            throw new Error('Already piping ' . get_class($this) . ' into ' . get_class($nodes[0]));
        }
    }
}
