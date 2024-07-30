<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Entities;

use CranachDigitalArchive\Importer\AbstractItemLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;
use CranachDigitalArchive\Importer\Modules\Drawings\Interfaces\IDrawingInfo;

/**
 * @template-extends AbstractItemLanguageCollection<IDrawingInfo>
 */
class DrawingInfoLanguageCollection extends AbstractItemLanguageCollection implements IDrawingInfo
{
    protected function createItem(): IDrawingInfo
    {
        return new DrawingInfo();
    }


    public function setMetadata(Metadata $metadata)
    {
        foreach ($this as $drawingInfo) {
            $drawingInfo->setMetadata($metadata);
        }
    }


    public function getMetadata(): ?Metadata
    {
        return $this->first()->getMetadata();
    }


    public function setInventoryNumber(string $inventoryNumber): void
    {
        foreach ($this as $drawingInfo) {
            $drawingInfo->setInventoryNumber($inventoryNumber);
        }
    }


    public function getInventoryNumber(): string
    {
        return $this->first()->getInventoryNumber();
    }


    public function addReference(ObjectReference $reference): void
    {
        foreach ($this as $drawingInfo) {
            $drawingInfo->addReference($reference);
        }
    }


    public function getReferences(): array
    {
        return $this->first()->getReferences();
    }
}
