<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Collectors;

use Error;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicInfo;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicInfoLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaReference;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

class LocationsCollector extends Consumer
{
    private $graphicInfoCollections = [];


    private function __construct()
    {
    }


    public static function new(

    ): self {
        return new self;
    }


    public function getAllGraphicInfos(): array
    {
        return $this->graphicInfoCollections;
    }

    public function handleItem($item): bool
    {
        if (!($item instanceof GraphicInfoLanguageCollection)) {
            echo get_class($item);
            throw new Error('Pushed item is not of the expected class \'GraphicInfoLanguageCollection\'');
        }

        $this->graphicInfoCollections[$item->getInventoryNumber()] = $item;

        return true;
    }


    public function getLocations(string $langCode, string $inventoryNumber): ?array
    {
        if (!isset($this->graphicInfoCollections[$inventoryNumber])) {
            return null;
        }

        $locations = [];

        /** @var GraphicInfo */
        $item = $this->graphicInfoCollections[$inventoryNumber]->get($langCode);

        /** @var \CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference */
        foreach ($item->getReprintReferences() as $reprintReference) {
            $reprintInventoryNumber = $reprintReference->getInventoryNumber();

            if (!isset($this->graphicInfoCollections[$reprintInventoryNumber])) {
                continue;
            }

            /** @var GraphicInfo */
            $reprintItem = $this->graphicInfoCollections[$reprintInventoryNumber]->get($langCode);

            /** @var MetaReference */
            foreach ($reprintItem->getLocations() as $location) {
                $matchingExistingLocations = array_filter(
                    $locations,
                    function (MetaReference $existingLocation) use ($location) {
                        return MetaReference::equal($existingLocation, $location);
                    },
                    ARRAY_FILTER_USE_BOTH
                );

                if (count($matchingExistingLocations) === 0) {
                    $locations[] = $location;
                }
            }
        }

        return $locations;
    }


    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }


    public function done(ProducerInterface $producer)
    {
        /* should never trigger an action on done */
    }


    public function cleanUp()
    {
        $this->graphicInfoCollections = [];
    }
}
