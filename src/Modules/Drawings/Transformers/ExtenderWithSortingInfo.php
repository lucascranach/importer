<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Drawings\Interfaces\IDrawing;
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\DrawingLanguageCollection;
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
        if (!($item instanceof DrawingLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'DrawingLanguageCollection\'');
        }

        /** @var IDrawing $subItem */
        foreach ($item as $subItem) {
            $subItem->setSearchSortingNumber($this->extractSortingInfo($subItem));
        }

        $this->next($item);
        return true;
    }

    private function extractSortingInfo(GraphicLanguageCollection $collection): string
    {
        $sortingNumber = $collection->getSortingNumber();
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
