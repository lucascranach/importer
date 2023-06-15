<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers;

use CranachDigitalArchive\Importer\Language;
use Error;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Publication;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces\ISearchableLiteratureReference;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithAggregatedSearchableData extends Hybrid
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
        if (!($item instanceof ISearchableLiteratureReference)) {
            throw new Error('Pushed item is not of expected interface \'ISearchableLiteratureReference\'');
        }

        /**
         * @var string $langCode
         * @var ISearchableLiteratureReference $searchableLiteratureReference
         */
        foreach ($item as $langCode => $searchableLiteratureReference) {
            $searchableLiteratureReference->setPublicationsLine(self::getPublicationsLine($langCode, $searchableLiteratureReference));
        }

        $this->next($item);
        return true;
    }

    private static function getPublicationsLine(string $langCode, ISearchableLiteratureReference $item): string
    {
        $publicationsTexts = array_map(function (Publication $publication) use ($langCode) {
            $publicationText = $publication->getText();

            return $langCode === Language::EN
                ? strtolower($publicationText)
                : $publicationText;
        }, $item->getPublications());
        return implode(', ', $publicationsTexts);
    }
}
