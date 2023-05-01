<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Entities;

use CranachDigitalArchive\Importer\Modules\Graphics\Interfaces\IGraphicInfo;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaLocationReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;

/**
 * Representing a single painting info
 */
class GraphicInfo implements IGraphicInfo
{
    const INVENTORY_NUMBER_PREFIX_PATTERNS = [
        '/^GWN_/' => 'GWN_',
        '/^CDA\./' => 'CDA.',
        '/^CDA_/' => 'CDA_',
        '/^G_G_/' => 'G_G_',
        '/^G_/' => 'G_',
    ];

    public $metadata = null;
    public $inventoryNumberPrefix = '';
    public $inventoryNumber = '';
    public $isVirtual = false;
    public $reprintReferences = [];
    public $locations = [];
    public $repository = '';


    public function __construct()
    {
    }

    public function setMetadata(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }


    public function getMetadata(): ?Metadata
    {
        return $this->metadata;
    }


    public function getInventoryNumberPrefix(): string
    {
        return $this->inventoryNumberPrefix;
    }


    public function setInventoryNumberPrefix(string $inventoryNumberPrefix)
    {
        $this->inventoryNumberPrefix = $inventoryNumberPrefix;
    }


    public function setInventoryNumber(string $inventoryNumber): void
    {
        $this->inventoryNumber = $inventoryNumber;

        foreach (self::INVENTORY_NUMBER_PREFIX_PATTERNS as $pattern => $value) {
            $counter = 0;

            $this->inventoryNumber = preg_replace($pattern, '', $this->inventoryNumber, -1, $counter);

            if ($counter > 0) {
                $this->setInventoryNumberPrefix($value);
                break;
            }
        }
    }


    public function getInventoryNumber(): string
    {
        return $this->inventoryNumber;
    }


    public function setIsVirtual(bool $isVirtual): void
    {
        $this->isVirtual = $isVirtual;
    }


    public function getIsVirtual(): bool
    {
        return $this->isVirtual;
    }


    public function addReprintReference(ObjectReference $reference): void
    {
        $this->reprintReferences[] = $reference;
    }


    public function setReprintReferences(array $references): void
    {
        $this->reprintReferences = $references;
    }


    public function getReprintReferences(): array
    {
        return $this->reprintReferences;
    }


    public function setLocations(array $locations): void
    {
        $this->locations = $locations;
    }


    public function addLocation(MetaLocationReference $location): void
    {
        $matchingExistingLocations = array_filter(
            $this->locations,
            function (MetaLocationReference $existingLocation) use ($location) {
                return MetaLocationReference::equal($existingLocation, $location);
            },
            ARRAY_FILTER_USE_BOTH
        );

        if (count($matchingExistingLocations) === 0) {
            $this->locations[] = $location;
        }
    }


    public function getLocations(): array
    {
        return $this->locations;
    }


    public function setRepository(string $repository): void
    {
        $this->repository = $repository;
    }


    public function getRepository(): string
    {
        return $this->repository;
    }
}
