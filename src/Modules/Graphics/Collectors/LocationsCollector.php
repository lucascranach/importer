<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Collectors;

use Error;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicInfo;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaReference;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

class LocationsCollector extends Consumer
{
    private $graphicInfos = [
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


    public function getAllGraphicInfos(): array
    {
        return $this->graphicInfos;
    }

    public function handleItem($item): bool
    {
        if (!($item instanceof GraphicInfo)) {
            echo get_class($item);
            throw new Error('Pushed item is not of the expected class \'GraphicInfo\'');
        }

        $metadata = $item->getMetadata();

        if (is_null($metadata)) {
            return false;
        }

        $langCode = $metadata->getLangCode();
        $inventoryNumber = $item->getInventoryNumber();

        $this->graphicInfos[$langCode][$inventoryNumber] = $item;

        return true;
    }


    public function getLocations(string $langCode, string $inventoryNumber): ?array
    {
        if (!isset($this->graphicInfos[$langCode][$inventoryNumber])) {
            return null;
        }

        $locations = [];

        /** @var GraphicInfo */
        $item = $this->graphicInfos[$langCode][$inventoryNumber];

        /** @var \CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference */
        foreach ($item->getReprintReferences() as $reprintReference) {
            $reprintInventoryNumber = $reprintReference->getInventoryNumber();

            if (!isset($this->graphicInfos[$langCode][$reprintInventoryNumber])) {
                continue;
            }

            /** @var GraphicInfo */
            $reprintItem = $this->graphicInfos[$langCode][$reprintInventoryNumber];

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
        $this->graphicInfos = [];
    }
}
