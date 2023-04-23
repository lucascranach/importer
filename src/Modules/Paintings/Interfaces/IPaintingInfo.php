<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Interfaces;

use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;

interface IPaintingInfo
{
    public function setMetadata(Metadata $metadata);

    public function getMetadata(): ?Metadata;

    public function setInventoryNumber(string $inventoryNumber): void;

    public function getInventoryNumber(): string;

    public function addReference(ObjectReference $reference): void;

    public function getReferences(): array;
}
