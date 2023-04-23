<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\PaintingLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search\SearchablePaintingLanguageCollection;
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
        if (!($item instanceof PaintingLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'PaintingLanguageCollection\'');
        }

        $this->next($this->mapToSearchablePainting($item));
        return true;
    }


    private function mapToSearchablePainting(PaintingLanguageCollection $paintingCollection): SearchablePaintingLanguageCollection
    {
        $searchablePaintingCollection = SearchablePaintingLanguageCollection::create();

        foreach ($paintingCollection as $langCode => $painting) {
            $searchablePainting = $searchablePaintingCollection->get($langCode);

            foreach (get_object_vars($painting) as $key => $value) {
                $searchablePainting->$key = $value;
            }
        }

        return $searchablePaintingCollection;
    }
}
