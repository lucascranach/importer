<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers;

use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces\ILiteratureReference;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use Error;

class MetadataFiller extends Hybrid
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
        if (!($item instanceof ILiteratureReference)) {
            throw new Error('Pushed item is not of expected interface \'ILiteratureReference\'');
        }

        foreach ($item as $literatureReference) {
            $metadata = $literatureReference->getMetadata();

            if (!is_null($metadata)) {
                $metadata->setId($item->getId());
                $metadata->setTitle($item->getTitle());
                $metadata->setSubtitle($item->getSubtitle());
                $metadata->setDate($item->getDate());
                $metadata->setClassification('');
                $metadata->setImgSrc('');
            }
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
