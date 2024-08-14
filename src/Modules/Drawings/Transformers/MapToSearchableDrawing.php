<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\DrawingLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\Search\SearchableDrawingLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class MapToSearchableDrawing extends Hybrid
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
        if (!($item instanceof DrawingLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'DrawingLanguageCollection\'');
        }

        $this->next($this->mapToSearchableDrawing($item));
        return true;
    }


    private function mapToSearchableDrawing(DrawingLanguageCollection $drawingCollection): SearchableDrawingLanguageCollection
    {
        $searchableDrawingCollection = SearchableDrawingLanguageCollection::create();

        foreach ($drawingCollection as $langCode => $drawing) {
            $searchableDrawing = $searchableDrawingCollection->get($langCode);

            foreach (get_object_vars($drawing) as $key => $value) {
                $searchableDrawing->$key = $value;
            }
        }

        return $searchableDrawingCollection;
    }
}
