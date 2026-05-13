<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Entities;

use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;
use CranachDigitalArchive\Importer\Modules\Drawings\Interfaces\IDrawingInfo;

/**
 * Representing a single drawing info
 */
class DrawingInfo implements IDrawingInfo
{
    const INVENTORY_NUMBER_PREFIX_PATTERNS = [
        '/^Z_/' => 'Z_',
    ];

    public $metadata = null;

    public $inventoryNumberPrefix = '';
    public $inventoryNumber = '';
    public $references = [];


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

    public function setInventoryNumberPrefix(string $inventoryNumberPrefix): void
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


    public function addReference(ObjectReference $reference): void
    {
        $this->references[] = $reference;
    }


    public function getReferences(): array
    {
        return $this->references;
    }
}
