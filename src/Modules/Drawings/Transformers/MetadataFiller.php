<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Transformers;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Drawings\Interfaces\IDrawing;
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\DrawingLanguageCollection;
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
        if (!($item instanceof DrawingLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'DrawingLanguageCollection\'');
        }

        /** @var string $langCode
        /** @var IDrawing $subItem */
        foreach ($item as $langCode => $subItem) {
            $this->extendMetadata($subItem);
        }

        $this->next($item);
        return true;
    }

    private function extendMetadata(IDrawing $item): void
    {
        $metadata = $item->getMetadata();

        if (is_null($metadata)) {
            return;
        }

        $titles = $item->getTitles();
        $title = isset($titles[0]) ? $titles[0]->getTitle() : '';

        $inventors = array_filter($item->getPersons(), function ($person) {
            return $person->getRole() === 'Künstler';
        });
        $inventor = current($inventors);
        $inventorName = $inventor ? $inventor->getName() : '';

        $dating = $item->getDating();
        $dated = !is_null($dating) ? $dating->getDated() : '';

        $classificationName = $item->getClassification()->getClassification();

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

        $metadata->setId($item->getId());
        $metadata->setTitle($title);
        $metadata->setSubtitle($inventorName);
        $metadata->setDate($dated);
        $metadata->addAdditionalInfo($classificationName);
        $metadata->addAdditionalInfo($item->getDimensions());
        $metadata->setClassification($classificationName);
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
