<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search\SearchableGraphic;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search\SearchableGraphicLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class MapToSearchableGraphic extends Hybrid
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
        if (!($item instanceof GraphicLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'GraphicLanguageCollection\'');
        }

        $this->next($this->mapToSearchableGraphic($item));
        return true;
    }


    private function mapToSearchableGraphic(GraphicLanguageCollection $graphicCollection): SearchableGraphicLanguageCollection
    {
        $searchableGraphicCollection = SearchableGraphicLanguageCollection::create();

        foreach ($graphicCollection as $langCode => $graphic) {
            $searchableGraphic = $searchableGraphicCollection->get($langCode);

            foreach (get_object_vars($graphic) as $key => $value) {
                $searchableGraphic->$key = $value;
            }
        }


        return $searchableGraphicCollection;
    }
}
