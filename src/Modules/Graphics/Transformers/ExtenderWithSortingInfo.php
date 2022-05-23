<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
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
        if (!($item instanceof Graphic)) {
            throw new Error('Pushed item is not of expected class \'Graphic\'');
        }

        $item->setSearchSortingNumber($this->extractSortingInfo($item));

        $this->next($item);
        return true;
    }

    private function extractSortingInfo(Graphic $item): string
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

        $year = array_shift($splitSortingNumber);
        $pos = intval(array_shift($splitSortingNumber)) + 1000;

        $updatedSortingNumber = implode('-', [$year, $pos, ...$splitSortingNumber]);

        return $updatedSortingNumber;
    }
}
