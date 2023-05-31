<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReference;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Publication;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces\ILiteratureReference;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithSubPublications extends Hybrid
{
    private static $greyLiteratureType = 'grey literature';
    private static $greyLiteratureSubPublicationTypes = [
        'thesis',
        'dissertation',
        'manuscript',
        'unpublished materials',
        'manuscript (document genre)',
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
        if (!($item instanceof ILiteratureReference)) {
            throw new Error('Pushed item is not of expected interface \'ILiteratureReference\'');
        }

        foreach ($item as $literatureReference) {
            self::reorganizeGreyLiteratureSubPublications($literatureReference);
        }

        $this->next($item);
        return true;
    }

    private static function reorganizeGreyLiteratureSubPublications(LiteratureReference $item): void
    {
        /** @var Publication */
        $greyLiteraturePublication = null;

        /** @var Publication[] */
        $greyLiteratureSubPublications = [];

        /** @var Publication[] */
        $remainingPublications = [];

        foreach ($item->getPublications() as $publication) {
            if (in_array($publication->type, self::$greyLiteratureSubPublicationTypes, true)) {
                $greyLiteratureSubPublications[] = $publication;
                continue;
            }

            if ($publication->type === self::$greyLiteratureType) {
                $greyLiteraturePublication = $publication;
            }

            $remainingPublications[] = $publication;
        }

        if (!is_null($greyLiteraturePublication) && count($greyLiteratureSubPublications) > 0) {
            $greyLiteraturePublication->setSubPublications($greyLiteratureSubPublications);
        }

        $item->setPublications($remainingPublications);
    }
}
