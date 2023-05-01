<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Interfaces;

interface ISearchablePainting extends IPainting
{
    public function addFilterInfoCategoryItems(string $categoryId, array $filterInfoItems): void;

    public function getFilterInfoCategoryItems(string $categoryId): ?array;

    public function getFilterInfoItems(): array;

    public function addInvolvedPersonsFullname(string $fullname): void;

    public function getInvolvedPersonsFullnames(): array;

    public function getFilterDating(): int;

    public function setFilterDating(int $dating): void;
}
