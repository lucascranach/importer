<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Archivals\Interfaces\IArchival;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\ArchivalLanguageCollection;
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
        if (!($item instanceof ArchivalLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'ArchivalLanguageCollection\'');
        }

        /** @var IArchival $subItem */
        foreach ($item as $subItem) {
            $subItem->setSearchSortingNumber($this->extractSortingInfo($subItem));
        }

        $this->next($item);
        return true;
    }

    private function extractSortingInfo(IArchival $item): string
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
