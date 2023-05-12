<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\ArchivalLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\Search\SearchableArchivalLanguageCollection;
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
        if (!($item instanceof ArchivalLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'ArchivalLanguageCollection\'');
        }

        $this->next($this->mapToSearchableArchival($item));
        return true;
    }


    private function mapToSearchableArchival(ArchivalLanguageCollection $archivalCollection): SearchableArchivalLanguageCollection
    {
        $searchableArchivalCollection = SearchableArchivalLanguageCollection::create();

        foreach ($archivalCollection as $langCode => $archival) {
            $searchableArchival = $searchableArchivalCollection->get($langCode);

            foreach (get_object_vars($archival) as $key => $value) {
                $searchableArchival->$key = $value;
            }
        }

        return $searchableArchivalCollection;
    }
}
