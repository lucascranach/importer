<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Collectors;

use Error;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\PaintingLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

class MetaReferenceCollector extends Consumer
{
    private $collection = [];


    private function __construct()
    {
    }


    public static function new(

    ): self {
        return new self;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof GraphicLanguageCollection) && !($item instanceof PaintingLanguageCollection)) {
            echo get_class($item);
            throw new Error('Pushed item is not of the expected class \'GraphicLanguageCollection\' or \'PaintingLanguageCollection\'');
        }

        foreach ($item as $subItem) {
            $this->collectAllKeywordsForItem($subItem);
        }

        return true;
    }


    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }


    public function done(IProducer $producer)
    {
        /* should never trigger an action on done */
    }


    public function cleanUp()
    {
        $this->collection = [];
    }


    public function getCollection(): array
    {
        return $this->collection;
    }


    private function collectAllKeywordsForItem($item): void
    {
        // TODO: REMOVE ME FOR ALL KEYWORD - LINKS
        // Skip on objects with some specific ids
        $metadata = $item->getMetadata();
        if (!is_null($metadata) && in_array($metadata->getId(), ['UEBERSCHREIBEN', 'UEBERSCHREIBEN01'], true)) {
            return;
        }

        // Skip objects without overall image category
        if (($item instanceof Graphic)) {
            $isVirtual = $item->getIsVirtual();

            // For graphics we only need the keywords for virtual graphics
            if (!$isVirtual) {
                return;
            }

            $images = (array)$item->getImages();
            if (!isset($images['overall'])) {
                return;
            }
        }
        // TODO: END

        foreach ($item->getKeywords() as $keyword) {
            $this->collection[$keyword->getTerm()] = $keyword;
        }
    }
}
