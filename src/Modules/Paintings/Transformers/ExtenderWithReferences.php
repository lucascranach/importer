<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Transformers;

use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Painting;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Paintings\Collectors\ReferencesCollector;
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
        if (!($item instanceof Painting)) {
            throw new Error('Pushed item is not of expected class \'Painting\'');
        }

        $metadata = $item->getMetadata();

        if (is_null($metadata)) {
            $this->next($item);
            return true;
        }

        $foundReferences = $this->referenceCollector->getItem(
            $metadata->getLangCode(),
            $item->getInventoryNumber()
        );


        if (!is_null($foundReferences) && count($foundReferences) > 0) {
            $item->setReferences($foundReferences);
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
