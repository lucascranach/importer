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

        [$year, $position] = $this->extractSortingInfo($item);
        $item->setSortingInfo($year, $position);

        $this->next($item);
        return true;
    }

    private function extractSortingInfo(Graphic $item): array
    {
        $sortingNumber = $item->getSortingNumber();
        $splitSortingNumber = array_filter(explode('-', $sortingNumber));

        if (count($splitSortingNumber) === 0) {
            return [0, 0];
        }

        $year = intval(array_shift($splitSortingNumber));

        $position = 4;
        $factors = [10000, 100, 10, 1];

        for ($i = 0; $i < count($splitSortingNumber); $i++) {
            $position += intval($splitSortingNumber[$i]) * $factors[$i];
        }

        return [$year, $position];
    }
}
