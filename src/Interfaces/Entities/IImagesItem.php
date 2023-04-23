<?php

namespace CranachDigitalArchive\Importer\Interfaces\Entities;

use CranachDigitalArchive\Importer\Modules\Main\Entities\Metadata;

/**
 * Representing an images item
 */
interface IImagesItem extends IBaseItem
{
    public function getMetadata(): ?Metadata;

    public function getRemoteId(): string;

    public function setImages(array $images): void;

    public function getImages();

    public function setDocuments(array $documents): void;

    public function getDocuments();
}
