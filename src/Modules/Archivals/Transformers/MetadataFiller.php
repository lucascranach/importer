<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Transformers;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\ArchivalLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Archivals\Interfaces\IArchival;
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
        if (!($item instanceof ArchivalLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'ArchivalLanguageCollection\'');
        }

        foreach ($item as $subItem) {
            $this->extendMetadata($subItem);
        }

        $this->next($item);
        return true;
    }


    private function extendMetadata(IArchival $item): void
    {
        $metadata = $item->getMetadata();

        if (is_null($metadata)) {
            $this->next($item);
            return;
        }

        $images = $item->getImages();
        if (is_array($images)) {
            $images = $images['representative'] ?? $images['overall'] ?? false;
        } else {
            $images = false;
        }
        $imageSrc = '';

        if ($images && count($images['images']) > 0) {
            $imageSrc = $images['images'][0]['sizes']['small']['src'];

            if (is_null($imageSrc)) {
                $imageSrc = '';
            }
        }

        $summaries = $item->getSummaries();
        $summary = $summaries[0] ?? '';
        $dating = $item->getDating();
        $dated = !is_null($dating) ? $dating->getDated() : '';

        $metadata->setId($item->getId());
        $metadata->setTitle($summary);
        $metadata->setSubtitle('');
        $metadata->setDate($dated);
        $metadata->setClassification('');
        $metadata->setImgSrc($imageSrc);
    }


    /**
     * @return void
     */
    public function done(IProducer $producer)
    {
        parent::done($producer);
    }
}
