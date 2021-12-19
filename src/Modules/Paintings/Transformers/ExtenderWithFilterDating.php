<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search\SearchablePainting;
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
        if (!($item instanceof SearchablePainting)) {
            throw new Error('Pushed item is not of expected class \'SearchablePainting\'');
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
