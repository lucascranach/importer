<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Transformers;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Paintings\Collectors\ReferencesCollector;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\PaintingLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use Error;

class ExtenderWithReferences extends Hybrid
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

        foreach ($item as $subItem) {
            $metadata = $subItem->getMetadata();

            if (is_null($metadata)) {
                $this->next($subItem);
                return true;
            }

            $foundReferences = $this->referenceCollector->getItem(
                $metadata->getLangCode(),
                $subItem->getInventoryNumber()
            );


            if (!is_null($foundReferences) && count($foundReferences) > 0) {
                $subItem->setReferences($foundReferences);
            }
        }

        $this->next($item);
        return true;
    }


    /**
     * @return void
     */
    public function done(ProducerInterface $producer)
    {
        parent::done($producer);
    }
}
