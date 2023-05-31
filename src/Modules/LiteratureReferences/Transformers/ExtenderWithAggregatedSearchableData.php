<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers;

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

        /** @var ISearchableLiteratureReference */
        foreach ($item as $searchableLiteratureReference) {
            $searchableLiteratureReference->setPublicationsLine(self::getPublicationsLine($searchableLiteratureReference));
        }

        $this->next($item);
        return true;
    }

    private static function getPublicationsLine(ISearchableLiteratureReference $item): string
    {
        $publicationsTexts = array_map(function (Publication $publication) {
            return $publication->getText();
        }, $item->getPublications());
        return implode(', ', $publicationsTexts);
    }
}
