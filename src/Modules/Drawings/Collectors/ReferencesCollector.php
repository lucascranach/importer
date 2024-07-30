<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Collectors;

use Error;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\DrawingInfoLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

class ReferencesCollector extends Consumer
{
    private $collection = [
        Language::DE => [],
        Language::EN => [],
    ];


    private function __construct()
    {
    }


    public static function new(

    ): self {
        return new self;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof DrawingInfoLanguageCollection)) {
            echo get_class($item);
            throw new Error('Pushed item is not of the expected class \'DrawingInfoLanguageCollection\'');
        }

        foreach ($item as $subItem) {
            $metadata = $subItem->getMetadata();

            if (is_null($metadata)) {
                return false;
            }

            $langCode = $metadata->getLangCode();
            $inventoryNumber = $subItem->getInventoryNumber();


            if (!isset($this->collection[$langCode][$inventoryNumber])) {
                $this->collection[$langCode][$inventoryNumber] = [];
            }

            $this->collection[$langCode][$inventoryNumber] = array_merge(
                $this->collection[$langCode][$inventoryNumber],
                $subItem->getReferences()
            );

            foreach ($subItem->getReferences() as $reference) {
                $referenceInventoryNumber = $reference->getInventoryNumber();

                if (!isset($this->collection[$langCode][$referenceInventoryNumber])) {
                    $this->collection[$langCode][$referenceInventoryNumber] = [];
                }

                $clonedReference = clone $reference;

                $clonedReference->setInventoryNumber($inventoryNumber);

                $this->collection[$langCode][$referenceInventoryNumber][] = $clonedReference;
            }
        }

        return true;
    }


    public function getItem(string $langCode, string $inventoryNumber)
    {
        if (isset($this->collection[$langCode][$inventoryNumber])) {
            return $this->collection[$langCode][$inventoryNumber];
        }

        return null;
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
}
