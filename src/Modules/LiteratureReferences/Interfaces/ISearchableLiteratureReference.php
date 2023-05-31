<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces;

interface ISearchableLiteratureReference extends ILiteratureReference
{
    public function setPublicationsLine(string $publicationsLine): void;

    public function getPublicationsLine(): string;
}
