<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces;

interface ISearchableLiteratureReference extends ILiteratureReference
{
    public function addFilterInfoCategoryItems(string $categoryId, array $filterInfoItems): void;

    public function getFilterInfoCategoryItems(string $categoryId): ?array;

    public function getFilterInfoItems(): array;

    public function setPublicationsLine(string $publicationsLine): void;

    public function getPublicationsLine(): string;
}
