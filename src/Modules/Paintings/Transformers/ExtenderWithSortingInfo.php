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

        [$year, $position] = $this->extractSortingInfo($item);
        $item->setSortingInfo($year, $position);

        $this->next($item);
        return true;
    }

    private function extractSortingInfo(Painting $item): array
    {
        $sortingNumber = $item->getSortingNumber();
        $splitSortingNumber = array_filter(explode('-', $sortingNumber));

        if (count($splitSortingNumber) === 0) {
            return [0, 0];
        }

        $year = intval(array_shift($splitSortingNumber));

        $position = 3;
        $factors = [1000, 100, 10, 1];

        for ($i = 0; $i < count($splitSortingNumber); $i++) {
            $position += intval($splitSortingNumber[$i]) * $factors[$i];
        }

        return [$year, $position];
    }
}
