<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Interfaces;

interface ISearchableGraphic extends IGraphic
{
    public function addFilterInfoCategoryItems(string $categoryId, array $filterInfoItems): void;

    public function getFilterInfoCategoryItems(string $categoryId): ?array;

    public function getFilterInfoItems(): array;

    public function addInvolvedPersonsFullname(string $fullname): void;

    public function getInvolvedPersonsFullnames(): array;

    public function getFilterDating(): int;

    public function setFilterDating(int $dating): void;

    public function getChildRepositories(): array;

    public function setChildRepositories(array $childRepositories): void;

    public function addChildRepository(string $childRepository): void;
}
