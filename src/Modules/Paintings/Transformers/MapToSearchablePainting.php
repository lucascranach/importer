<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Painting;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search\SearchablePainting;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class MapToSearchablePainting extends Hybrid
{
    private function __construct()
    {
    }


    public static function new(): self
    {
        $transformer = new self;

        return $transformer;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof Painting)) {
            throw new Error('Pushed item is not of expected class \'Painting\'');
        }

        $this->next($this->mapToSearchablePainting($item));
        return true;
    }


    private function mapToSearchablePainting(Painting $painting): SearchablePainting
    {
        $searchablePainting = new SearchablePainting();

        foreach (get_object_vars($painting) as $key => $value) {
            $searchablePainting->$key = $value;
        }

        return $searchablePainting;
    }
}
