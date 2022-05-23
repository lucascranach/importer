<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Painting;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithSortingInfo extends Hybrid
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
        if (!($item instanceof Painting)) {
            throw new Error('Pushed item is not of expected class \'Painting\'');
        }

        $item->setSearchSortingNumber($this->extractSortingInfo($item));

        $this->next($item);
        return true;
    }

    private function extractSortingInfo(Painting $item): string
    {
        $sortingNumber = $item->getSortingNumber();
        $splitSortingNumber = array_filter(
            array_map(
                'trim',
                explode('-', $sortingNumber)
            )
        );

        if (count($splitSortingNumber) === 0) {
            return '3000';
        }

        return $sortingNumber;
    }
}
