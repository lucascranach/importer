<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Entities\Search;

use CranachDigitalArchive\Importer\Modules\Drawings\Entities\DrawingLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Drawings\Interfaces\ISearchableDrawing;

class SearchableDrawingLanguageCollection extends DrawingLanguageCollection implements ISearchableDrawing
{
    protected function createItem(): SearchableDrawing
    {
        return new SearchableDrawing();
    }

    public function addFilterInfoCategoryItems(string $categoryId, array $filterInfoItems): void
    {
        /** @var ISearchableDrawing */
        foreach ($this as $searchableDrawing) {
            $searchableDrawing->addFilterInfoCategoryItems($categoryId, $filterInfoItems);
        }
    }

    public function getFilterInfoCategoryItems(string $categoryId): ?array
    {
        /** @var ISearchableDrawing */
        $first = $this->first();
        return $first->getFilterInfoCategoryItems($categoryId);
    }

    public function getFilterInfoItems(): array
    {
        /** @var ISearchableDrawing */
        $first = $this->first();
        return $first->getFilterInfoItems();
    }

    public function addInvolvedPersonsFullname(string $fullname): void
    {
        /** @var ISearchableDrawing */
        foreach ($this as $searchableDrawing) {
            $searchableDrawing->addInvolvedPersonsFullname($fullname);
        }
    }

    public function getInvolvedPersonsFullnames(): array
    {
        /** @var ISearchableDrawing */
        $first = $this->first();
        return $first->getInvolvedPersonsFullnames();
    }

    public function getFilterDating(): int
    {
        /** @var ISearchableDrawing */
        $first = $this->first();
        return $first->getFilterDating();
    }

    public function setFilterDating(int $dating): void
    {
        /** @var ISearchableDrawing */
        foreach ($this as $searchableDrawing) {
            $searchableDrawing->setFilterDating($dating);
        }
    }
}
