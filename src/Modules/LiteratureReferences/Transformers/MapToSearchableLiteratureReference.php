<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReferenceLanguageCollection;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Search\SearchableLiteratureReferenceLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class MapToSearchableLiteratureReference extends Hybrid
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
        if (!($item instanceof LiteratureReferenceLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'LiteratureReferenceLanguageCollection\'');
        }

        $this->next($this->mapToSearchableLiteratureReference($item));
        return true;
    }


    private function mapToSearchableLiteratureReference(LiteratureReferenceLanguageCollection $literatureReferenceCollection): SearchableLiteratureReferenceLanguageCollection
    {
        $searchableLiteratureReferenceCollection = SearchableLiteratureReferenceLanguageCollection::create();

        foreach ($literatureReferenceCollection as $langCode => $archival) {
            $searchableLiteratureReference = $searchableLiteratureReferenceCollection->get($langCode);

            foreach (get_object_vars($archival) as $key => $value) {
                $searchableLiteratureReference->$key = $value;
            }
        }

        return $searchableLiteratureReferenceCollection;
    }
}
