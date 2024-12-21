<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Transformers;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Paintings\Collectors\ReferencesCollector;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\PaintingLanguageCollection;
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
        if (!($item instanceof PaintingLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'PaintingLanguageCollection\'');
        }

        $references = [
            "relatedInContentTo"=>[],
            "similarTo"=>[],
            "belongsTo"=>[],
            "partOfWork"=>[],
            "counterpartTo"=>[],
            "graphic"=>[],
            "all"=> []
        ];

        foreach ($item as $subItem) {
            $subItemReferences = $subItem->getReferences();
            foreach ($subItemReferences as $referenceItem) {
                
                if (in_array($referenceItem->getInventoryNumber(), $references['all'], true)) {
                    continue;
                }
                $references['all'][] = $referenceItem->getInventoryNumber();

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
