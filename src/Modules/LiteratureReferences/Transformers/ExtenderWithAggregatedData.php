<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Person;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReference;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces\ILiteratureReference;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithAggregatedData extends Hybrid
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
        if (!($item instanceof ILiteratureReference)) {
            throw new Error('Pushed item is not of expected interface \'ILiteratureReference\'');
        }

        foreach ($item as $literatureReference) {
            $literatureReference->setAuthors(self::getAuthors($literatureReference));
        }

        $this->next($item);
        return true;
    }

    private static function getAuthors(LiteratureReference $item): string
    {
        $personNames = array_map(function (Person $person) {
            return $person->getName();
        }, $item->getPersons());
        return implode(', ', $personNames);
    }
}
