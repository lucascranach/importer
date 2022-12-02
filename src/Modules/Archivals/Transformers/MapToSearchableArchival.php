<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\Archival;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\Search\SearchableArchival;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class MapToSearchableArchival extends Hybrid
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
        if (!($item instanceof Archival)) {
            throw new Error('Pushed item is not of expected class \'Archival\'');
        }

        $this->next($this->mapToSearchableArchival($item));
        return true;
    }


    private function mapToSearchableArchival(Archival $archival): SearchableArchival
    {
        $searchableArchival = new SearchableArchival();

        foreach (get_object_vars($archival) as $key => $value) {
            $searchableArchival->$key = $value;
        }

        return $searchableArchival;
    }
}
