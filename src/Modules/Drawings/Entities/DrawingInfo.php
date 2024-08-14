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
    public $metadata = null;

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

    public function setInventoryNumber(string $inventoryNumber): void
    {
        $this->inventoryNumber = $inventoryNumber;
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
