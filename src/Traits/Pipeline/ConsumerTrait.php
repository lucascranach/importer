<?php

namespace CranachDigitalArchive\Importer\Traits\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use Error;


const DEFAULT_INPUT_NAME = '::DEFAULT';

trait ConsumerTrait
{

	private $connectedProducersToInputs = [];


	public function getInputs(): array
	{
		return [
			DEFAULT_INPUT_NAME,
		];
	}


	public function registerProducerOnInput(ProducerInterface &$producer, ?string $input = null)
	{
		if (is_null($input)) {
			$input = DEFAULT_INPUT_NAME;
		}

		$inputs = $this->getInputs();

		if (!in_array($input, $inputs)) {
			if ($input === DEFAULT_INPUT_NAME) {
				throw new Error(
					'Producer ' . get_class($producer) .
					' connecting to non-existing default input of consumer ' . get_class($this),
				);
			}

			throw new Error(
				'Producer ' . get_class($producer) .
				' connecting to unknown input ' . $input .
				' of consumer ' . get_class($this),
			);
		}

		$this->connectedProducersToInputs[$input] = &$producer;
	}


	public function unregisterProducerOnInput(string $input): bool
	{
		if (!isset($this->connectedProducersToInputs[$input])) {
			return false;
		}

		unset($this->connectedProducersToInputs[$input]);

		return true;
	}


	public function getProducerOnInput(string $input): ?ProducerInterface
	{
		if (!isset($this->connectedProducersToInputs[$input])) {
			return null;
		}

		return $this->connectedProducersToInputs[$input];
	}


	public function hasConnectedProducerOnInput(string $input): bool
	{
		return !is_null($this->getProducerOnInput($input));
	}

}