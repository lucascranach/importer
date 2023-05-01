<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search\SearchableGraphicLanguageCollection;
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
        if (!($item instanceof SearchableGraphicLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'SearchableGraphicLanguageCollection\'');
        }

        /** @var \CranachDigitalArchive\Importer\Modules\Graphics\Interfaces\ISearchableGraphic */
        foreach ($item as $searchableGraphic) {
            /** @var \CranachDigitalArchive\Importer\Modules\Main\Entities\Person */
            foreach ($searchableGraphic->getPersons() as $person) {
                $fullname = trim($person->getPrefix()) . trim($person->getName()) . trim($person->getSuffix());

                if (!empty($fullname)) {
                    $searchableGraphic->addInvolvedPersonsFullname($fullname);
                }
            }
        }

        $this->next($item);
        return true;
    }
}
