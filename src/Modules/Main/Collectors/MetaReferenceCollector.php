<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Collectors;

use Error;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Painting;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

class MetaReferenceCollector extends Consumer
{
    private $collection = [];


    private function __construct()
    {
    }


    public static function new(

    ): self {
        return new self;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof Graphic) && !($item instanceof Painting)) {
            echo get_class($item);
            throw new Error('Pushed item is not of the expected class \'Graphic\' or \'Painting\'');
        }

        foreach ($item->getKeywords() as $keyword) {
            $this->collection[$keyword->getTerm()] = $keyword;
        }

        return true;
    }


    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }


    public function done(ProducerInterface $producer)
    {
        /* should never trigger an action on done */
    }


    public function cleanUp()
    {
        $this->collection = [];
    }


    public function getCollection(): array
    {
        return $this->collection;
    }
}