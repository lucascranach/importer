<?php

namespace CranachDigitalArchive\Importer\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\NodeInterface;


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


	public function addNodes(NodeInterface ...$nodes)
	{
		$this->nodes = $nodes;

		return $this;
	}


	public function start()
	{
		/* Only producers */
		$producers = $this->filterForProducers($this->nodes);

		foreach($producers as $producer) {
			$producer->run();
		}
	}

	private function filterForProducers($nodes)
	{
		return array_filter(
			$nodes,
			function($node) {
				return $node instanceof Producer;
			}
		);
	}

}