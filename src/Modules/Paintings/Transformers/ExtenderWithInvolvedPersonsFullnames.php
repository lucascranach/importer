<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search\SearchablePaintingLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithInvolvedPersonsFullnames extends Hybrid
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
        if (!($item instanceof SearchablePaintingLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'SearchablePaintingLanguageCollection\'');
        }

        foreach ($item as $subItem) {
            /** @var \CranachDigitalArchive\Importer\Modules\Main\Entities\Person */
            foreach ($subItem->getPersons() as $person) {
                $fullname = trim($person->getPrefix()) . trim($person->getName()) . trim($person->getSuffix());

                if (!empty($fullname)) {
                    $subItem->addInvolvedPersonsFullname($fullname);
                }
            }
        }

        $this->next($item);
        return true;
    }
}
