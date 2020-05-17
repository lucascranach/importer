<?php

namespace CranachDigitalArchive\Importer\Traits\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\{NodeInterface, ConsumerInterface, ProducerInterface};
use Error;


const DEFAULT_INPUT_NAME = '::DEFAULT';

trait ProducerTrait
{

	private $consumers = [];
	private $done = false;

	public function isReady(): bool {
		return true;
	}

	/*
	public function pipeChain(ConsumerInterface ...$nodes): ConsumerInterface
	{
		if (count($nodes) === 0) {
			throw new Error('At least one node expected to build the pipe up');
		}
		$this->checkForCyclicChains(...$nodes);
		$this->checkForExistingConnection(...$nodes);

		$firstNode = current($nodes);
		$this->consumer[] = $firstNode;
		$firstNode->registerProducerOnInput($this);
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
	*/


	public function pipe(ConsumerInterface $node, string $input = DEFAULT_INPUT_NAME): ConsumerInterface
	{
		$this->checkForExistingConnection($node);

		$this->consumers[] = (object)[
			'node' => $node,
			'input' => $input,
		];
		$node->registerProducerOnInput($this, $input);

		return $node;
	}


	public function next($data)
	{
		foreach ($this->consumers as $consumer) {
			if (!$consumer->node->handleItem($data, $consumer->input)) {
				throw new Error('Consumer ' . get_class($consumer->node) . ' could not handle item');
			}
		}
	}


	public function getConsumerNodes(): array
	{
		return $this->consumers;
	}


	public function isDone(): bool
	{
		return $this->done;
	}


	public function notifyError($error)
	{
		foreach ($this->consumers as $consumer) {
			$consumer->node->error($error);
		}
	}


	public function notifyDone(ProducerInterface $producer = null)
	{
		$this->done = true;
		$srcProducer = !is_null($producer) ? $producer : $this;

		foreach ($this->consumers as $consumer) {
			$consumer->node->done($srcProducer);
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

	public function checkForExistingConnection(NodeInterface $node)
	{
		foreach ($this->consumers as $consumer) {
			if ($node === $consumer->node) {
				throw new Error('Already piping ' . get_class($this) . ' into ' . get_class($node));
			}
		}
	}

}
