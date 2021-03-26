<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
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
        if (!($item instanceof Graphic)) {
            throw new Error('Pushed item is not of expected class \'Graphic\'');
        }

        $metadata = $item->getMetadata();

        if (is_null($metadata)) {
            $this->next($item);
            return true;
        }

        $titles = $item->getTitles();
        $title = isset($titles[0]) ? $titles[0]->getTitle() : '';

        $inventors = array_filter($item->getPersons(), function ($person) {
            return $person->getRole() === 'Inventor';
        });
        $inventor = current($inventors);
        $inventorName = $inventor ? $inventor->getName() : '';

        $dating = $item->getDating();
        $dated = !is_null($dating) ? $dating->getDated() : '';

        $classification = $item->getClassification();
        $classificationName = !is_null($classification) ? $classification->getClassification() : '';
        $printProcess = !is_null($classification) ? $classification->getPrintProcess() : '';

        if (!empty($printProcess)) {
            $classificationName .= ', ' . $printProcess;
        }

        $images = $item->getImages();
        $images = $images['representative'] ?? $images['overall'] ?? false;
        $imageSrc = '';

        if ($images && count($images['variants']) > 0) {
            $imageSrc = $images['variants'][count($images['variants']) - 1]['s']['src'];

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
