<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search\SearchableGraphic;
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
        if (!($item instanceof SearchableGraphic)) {
            throw new Error('Pushed item is not of expected class \'SearchableGraphic\'');
        }

        /** @var \CranachDigitalArchive\Importer\Modules\Main\Entities\Person */
        foreach ($item->getPersons() as $person) {
            $fullname = trim($person->getPrefix()) . trim($person->getName()) . trim($person->getSuffix());

            if (!empty($fullname)) {
                $item->addInvolvedPersonsFullname($fullname);
            }
        }

        $this->next($item);
        return true;
    }
}
