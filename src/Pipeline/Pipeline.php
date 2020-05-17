<?php

namespace CranachDigitalArchive\Importer\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\{NodeInterface, ProducerInterface};
use Error;


class Pipeline
{

	private $nodes = [];


	private function __construct()
	{
	}


	public static function new()
	{
		return new self;
	}


	public function withNodes(NodeInterface ...$nodes): Pipeline
	{
		$this->nodes = array_merge($this->nodes, $nodes);

		return $this;
	}


	public function start()
	{
		/* Only producers */
		$producers = $this->filterForProducers($this->nodes);
		foreach($producers as $producer) {
			if (!$producer->isReady()) {
				throw new Error('Producer ' . get_class($producer) . ' is not ready!');
			}
		}

		/* Only producers with a start procedure */
		$contentProducers = $this->filterForContentProducers($this->nodes);

		foreach($contentProducers as $contentProducer) {
			$contentProducer->run();
		}
	}

	private function filterForProducers($nodes): array
	{
		return array_filter(
			$nodes,
			function($node) {
				return $node instanceof ProducerInterface;
			}
		);
	}

	private function filterForContentProducers($nodes): array
	{
		return array_filter(
			$nodes,
			function($node) {
				return $node instanceof Producer;
			}
		);
	}

}