<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers;

use Error;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Person;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReference;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithAggregatedData extends Hybrid
{
    private const TEXT_CATEGORY_TYPE_JOURNAL = 'JOURNAL';
    private const TEXT_CATEGORY_TYPE_ARTICLE = 'ARTICLE';
    private const TEXT_CATEGORY_TYPE_MONOGRAPHY = 'MONOGRAPHY';

    private static $textCategoyTranslations = [
        Language::DE => [
            self::TEXT_CATEGORY_TYPE_JOURNAL => 'Zeitschrift',
            self::TEXT_CATEGORY_TYPE_ARTICLE => 'Aufsatz',
            self::TEXT_CATEGORY_TYPE_MONOGRAPHY => 'Monographie / Sammelband',
        ],
        Language::EN => [
            self::TEXT_CATEGORY_TYPE_JOURNAL => 'Journal',
            self::TEXT_CATEGORY_TYPE_ARTICLE => 'Article',
            self::TEXT_CATEGORY_TYPE_MONOGRAPHY => 'Monography / Miscellany',
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
        $item->setTextCategoryType($this->getTextCategoryType($item));
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

    private function getTextCategoryType(LiteratureReference $item): string
    {
        if ($item->journal) {
            return self::TEXT_CATEGORY_TYPE_JOURNAL;
        } elseif ($item->subtitle) {
            return self::TEXT_CATEGORY_TYPE_ARTICLE;
        } else {
            return self::TEXT_CATEGORY_TYPE_MONOGRAPHY;
        }
    }

    private function getTextCategory(LiteratureReference $item): string
    {
        $metadata = $item->getMetadata();

        if (is_null($metadata)) {
            return '';
        }

        $langCode = $metadata->getLangCode();

        $textCategoryType = $this->getTextCategoryType($item);
        return self::$textCategoyTranslations[$langCode][$textCategoryType];
    }
}
