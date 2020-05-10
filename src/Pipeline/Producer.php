<?php

namespace CranachDigitalArchive\Importer\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Traits\Pipeline\ProducerTrait;


abstract class Producer implements ProducerInterface
{

	use ProducerTrait;

	public abstract function run();

}
