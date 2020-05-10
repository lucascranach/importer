<?php

namespace CranachDigitalArchive\Importer\Interfaces\Pipeline;


/* Describing a consumer */
interface ConsumerInterface extends NodeInterface
{

	public function handleItem($item): bool;
	public function error($error);
	public function done(ProducerInterface $producer);

}