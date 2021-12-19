<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search\SearchableGraphic;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithFilterDating extends Hybrid
{
    private function __construct()
    {
    }


    public static function new(): self
    {
        return new self;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof SearchableGraphic)) {
            throw new Error('Pushed item is not of expected class \'SearchableGraphic\'');
        }

        $sortingNumber = $item->getSortingNumber();
        $matches = [];
        if (preg_match('/^(\d{4})-.*$/', $sortingNumber, $matches) && count($matches) !== 0) {
            $dating = intval($matches[1]);
            $item->setFilterDating($dating);
        }

        $this->next($item);
        return true;
    }
}
