<?php

namespace CranachDigitalArchive\Importer\Interfaces\Pipeline;


/* Describing a consumer */
interface ConsumerInterface extends NodeInterface
{

	public function getInputs(): array;
	public function registerProducerOnInput(ProducerInterface &$producer, ?string $input);
	public function unregisterProducerOnInput(string $input): bool;
	public function getProducerOnInput(string $input): ?ProducerInterface;
	public function hasConnectedProducerOnInput(string $input): bool;
	public function handleItem($item, string $input): bool;
	public function error($error);
	public function done(ProducerInterface $producer);

}