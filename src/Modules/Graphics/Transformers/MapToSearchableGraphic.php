<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search\SearchableGraphic;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class mapToSearchableGraphic extends Hybrid
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
        if (!($item instanceof Graphic)) {
            throw new Error('Pushed item is not of expected class \'Graphic\'');
        }

        $this->next($this->mapToSearchableGraphic($item));
        return true;
    }


    private function mapToSearchableGraphic(Graphic $graphic): SearchableGraphic
    {
        $searchableGraphic = new SearchableGraphic();

        foreach (get_object_vars($graphic) as $key => $value) {
            $searchableGraphic->$key = $value;
        }

        return $searchableGraphic;
    }
}
