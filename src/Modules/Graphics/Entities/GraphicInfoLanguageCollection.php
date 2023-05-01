<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Entities;

use CranachDigitalArchive\Importer\AbstractItemLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaLocationReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;
use CranachDigitalArchive\Importer\Modules\Graphics\Interfaces\IGraphicInfo;

/**
 * @template-extends AbstractItemLanguageCollection<IGraphicInfo>
 */
class GraphicInfoLanguageCollection extends AbstractItemLanguageCollection implements IGraphicInfo
{
    protected function createItem(): IGraphicInfo
    {
        return new GraphicInfo();
    }

    public function setMetadata(Metadata $metadata)
    {
        foreach ($this as $graphicInfo) {
            $graphicInfo->setMetadata($metadata);
        }
    }

    public function getMetadata(): ?Metadata
    {
        return $this->first()->getMetadata();
    }

    public function getInventoryNumberPrefix(): string
    {
        return $this->first()->getInventoryNumberPrefix();
    }

    public function setInventoryNumberPrefix(string $inventoryNumberPrefix)
    {
        foreach ($this as $graphicInfo) {
            $graphicInfo->setInventoryNumberPrefix($inventoryNumberPrefix);
        }
    }

    public function setInventoryNumber(string $inventoryNumber): void
    {
        foreach ($this as $graphicInfo) {
            $graphicInfo->setInventoryNumber($inventoryNumber);
        }
    }

    public function getInventoryNumber(): string
    {
        return $this->first()->getInventoryNumber();
    }

    public function setIsVirtual(bool $isVirtual): void
    {
        foreach ($this as $graphicInfo) {
            $graphicInfo->setIsVirtual($isVirtual);
        }
    }

    public function getIsVirtual(): bool
    {
        return $this->first()->getIsVirtual();
    }

    public function addReprintReference(ObjectReference $reference): void
    {
        foreach ($this as $graphicInfo) {
            $graphicInfo->addReprintReference($reference);
        }
    }

    public function setReprintReferences(array $references): void
    {
        foreach ($this as $graphicInfo) {
            $graphicInfo->setReprintReferences($references);
        }
    }

    public function getReprintReferences(): array
    {
        return $this->first()->getReprintReferences();
    }

    public function setLocations(array $locations): void
    {
        foreach ($this as $graphicInfo) {
            $graphicInfo->setLocations($locations);
        }
    }

    public function addLocation(MetaLocationReference $location): void
    {
        foreach ($this as $graphicInfo) {
            $graphicInfo->addLocation($location);
        }
    }

    public function getLocations(): array
    {
        return $this->first()->getLocations();
    }

    public function setRepository(string $repository): void
    {
        foreach ($this as $graphicInfo) {
            $graphicInfo->setRepository($repository);
        }
    }

    public function getRepository(): string
    {
        return $this->first()->getRepository();
    }
}
