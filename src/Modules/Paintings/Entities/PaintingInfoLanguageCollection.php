<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Entities;

use CranachDigitalArchive\Importer\AbstractItemLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;
use CranachDigitalArchive\Importer\Modules\Paintings\Interfaces\IPaintingInfo;

/**
 * @template-extends AbstractItemLanguageCollection<IPaintingInfo>
 */
class PaintingInfoLanguageCollection extends AbstractItemLanguageCollection implements IPaintingInfo
{
    protected function createItem(): IPaintingInfo
    {
        return new PaintingInfo();
    }


     public function setMetadata(Metadata $metadata)
     {
         foreach ($this as $paintingInfo) {
             $paintingInfo->setMetadata($metadata);
         }
     }


    public function getMetadata(): ?Metadata
    {
        return $this->first()->getMetadata();
    }


    public function setInventoryNumber(string $inventoryNumber): void
    {
        foreach ($this as $paintingInfo) {
            $paintingInfo->setInventoryNumber($inventoryNumber);
        }
    }


    public function getInventoryNumber(): string
    {
        return $this->first()->getInventoryNumber();
    }


    public function addReference(ObjectReference $reference): void
    {
        foreach ($this as $paintingInfo) {
            $paintingInfo->addReference($reference);
        }
    }


    public function getReferences(): array
    {
        return $this->first()->getReferences();
    }
}
