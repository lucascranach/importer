<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers;

use Error;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Person;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReference;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithAggregatedData extends Hybrid
{
    private static $textCategoyTranslations = [
        Language::DE => [
            'journal' => 'Zeitschrift',
            'article' => 'Aufsatz',
            'monography' => 'Monographie / Sammelband',
        ],
        Language::EN => [
            'journal' => 'Journal',
            'article' => 'Article',
            'monography' => 'Monography / Miscellany',
        ],
    ];

    private function __construct()
    {
    }


    public static function new(): self
    {
        return new self;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof LiteratureReference)) {
            throw new Error('Pushed item is not of expected class \'LiteratureReference\'');
        }

        $item->setAuthors($this->getAuthors($item));
        $item->setTextCategory($this->getTextCategory($item));

        $this->next($item);
        return true;
    }

    private function getAuthors(LiteratureReference $item): string
    {
        $personNames = array_map(function (Person $person) {
            return $person->getName();
        }, $item->getPersons());
        return implode(', ', $personNames);
    }

    private function getTextCategory(LiteratureReference $item): string
    {
        $metadata = $item->getMetadata();

        if (is_null($metadata)) {
            return '';
        }

        $langCode = $metadata->getLangCode();

        if ($item->journal) {
            return self::$textCategoyTranslations[$langCode]['journal'];
        } elseif ($item->subtitle) {
            return self::$textCategoyTranslations[$langCode]['article'];
        } else {
            return self::$textCategoyTranslations[$langCode]['monography'];
        }
    }
}
