<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Interfaces;

use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaLocationReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;

interface IGraphicInfo
{
    public function setMetadata(Metadata $metadata);

    public function getMetadata(): ?Metadata;

    public function getInventoryNumberPrefix(): string;

    public function setInventoryNumberPrefix(string $inventoryNumberPrefix);

    public function setInventoryNumber(string $inventoryNumber): void;

    public function getInventoryNumber(): string;

    public function setIsVirtual(bool $isVirtual): void;

    public function getIsVirtual(): bool;

    public function addReprintReference(ObjectReference $reference): void;

    public function setReprintReferences(array $references): void;

    public function getReprintReferences(): array;

    public function setLocations(array $locations): void;

    public function addLocation(MetaLocationReference $location): void;

    public function getLocations(): array;

    public function setRepository(string $repository): void;

    public function getRepository(): string;
}
