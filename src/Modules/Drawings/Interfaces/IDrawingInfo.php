<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Interfaces;

use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;

interface IDrawingInfo
{
    public function setMetadata(Metadata $metadata);

    public function getMetadata(): ?Metadata;

    public function getInventoryNumberPrefix(): string;

    public function setInventoryNumberPrefix(string $inventoryNumberPrefix): void;

    public function setInventoryNumber(string $inventoryNumber): void;

    public function getInventoryNumber(): string;

    public function addReference(ObjectReference $reference): void;

    public function getReferences(): array;
}
