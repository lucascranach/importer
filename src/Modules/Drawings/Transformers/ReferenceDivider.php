<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Transformers;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Drawings\Collectors\ReferencesCollector;
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\DrawingLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use Error;

class ReferenceDivider extends Hybrid
{
    private $referenceCollector;

    private function __construct(ReferencesCollector $referencesCollector)
    {
        $this->referenceCollector = $referencesCollector;
    }


    public static function new(ReferencesCollector $referencesCollector): self
    {
        return new self($referencesCollector);
    }



    public function handleItem($item): bool
    {
        if (!($item instanceof DrawingLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'DrawingLanguageCollection\'');
        }

        $references = [
            "relatedInContentTo"=>[],
            "similarTo"=>[],
            "belongsTo"=>[],
            "partOfWork"=>[],
            "counterpartTo"=>[],
            "graphic"=>[],
            "onSameSheet"=>[],
            "identicalWatermark"=>[],
            "partOfSerie"=>[]
        ];

        foreach ($item as $subItem) {
            $subItemReferences = $subItem->getReferences();
            foreach ($subItemReferences as $referenceItem) {
                if ($referenceItem->kind === 'RELATED_IN_CONTENT_TO') {
                    $references['relatedInContentTo'][] = $referenceItem;
                } elseif ($referenceItem->kind === 'SIMILAR_TO') {
                    $references['similarTo'][] = $referenceItem;
                } elseif ($referenceItem->kind === 'BELONGS_TO') {
                    $references['belongsTo'][] = $referenceItem;
                } elseif ($referenceItem->kind === 'PART_OF_WORK') {
                    $references['partOfWork'][] = $referenceItem;
                } elseif ($referenceItem->kind === 'COUNTERPART_TO') {
                    $references['counterpartTo'][] = $referenceItem;
                } elseif ($referenceItem->kind === 'GRAPHIC') {
                    $references['graphic'][] = $referenceItem;
                } elseif ($referenceItem->kind === 'ON_SAME_SHEET') {
                    $references['onSameSheet'][] = $referenceItem;
                } elseif ($referenceItem->kind === 'IDENTICAL_WATERMARK') {
                    $references['identicalWatermark'][] = $referenceItem;
                } elseif ($referenceItem->kind === 'PART_OF_SERIES') {
                    $references['partOfSerie'][] = $referenceItem;
                }
            }
            $subItem->setReferences($references);
        }

        $this->next($item);
        return true;
    }


    /**
     * @return void
     */
    public function done(IProducer $producer)
    {
        parent::done($producer);
    }
}
