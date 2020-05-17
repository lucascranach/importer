<?php

namespace CranachDigitalArchive\Importer\Traits\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\{NodeInterface, ConsumerInterface, ProducerInterface};
use Error;


trait ProducerTrait
{

	private $consumerNodes = [];
	private $done = false;

	public function isReady(): bool {
		return true;
	}

	public function pipe(ConsumerInterface ...$nodes): ConsumerInterface
	{
		if (count($nodes) === 0) {
			throw new Error('At least one node expected to build the pipe up');
		}
		$this->checkForCyclicChains(...$nodes);
		$this->checkForExistingConnection(...$nodes);

		$firstNode = current($nodes);
		$this->consumerNodes[] = $firstNode;
		$firstNode->registerProducerNode($this);
		$lastNode = end($nodes);
		reset($nodes);

		while (current($nodes) !== $lastNode) {
			$currentNode = current($nodes);
			$nextNode = next($nodes);

			if ($currentNode instanceof ProducerInterface) {
				$currentNode->pipe($nextNode);
			} else {
				throw new Error(
					'Non-Producer is only allowed as last item in the pipe-chain: ' . get_class($currentNode),
				);
			}
		}

		return $lastNode;
	}


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


	public function notifyError($error)
	{
		foreach ($this->consumerNodes as $consumerNode) {
			$consumerNode->error($error);
		}
	}


	public function notifyDone(ProducerInterface $producer = null)
	{
		$this->done = true;
		$srcProducer = !is_null($producer) ? $producer : $this;

		foreach ($this->consumerNodes as $consumerNode) {
			$consumerNode->done($srcProducer);
		}
	}


	public function checkForCyclicChains(NodeInterface ...$nodes)
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

	public function checkForExistingConnection(NodeInterface ...$nodes) {
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
