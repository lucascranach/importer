<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search;

use CranachDigitalArchive\Importer\Modules\Paintings\Entities\PaintingLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Paintings\Interfaces\ISearchablePainting;

class SearchablePaintingLanguageCollection extends PaintingLanguageCollection implements ISearchablePainting
{
    protected function createItem(): SearchablePainting
    {
        return new SearchablePainting();
    }

    public function addFilterInfoCategoryItems(string $categoryId, array $filterInfoItems): void
    {
        /** @var ISearchablePainting */
        foreach ($this as $searchablePainting) {
            $searchablePainting->addFilterInfoCategoryItems($categoryId, $filterInfoItems);
        }
    }

    public function getFilterInfoCategoryItems(string $categoryId): ?array
    {
        /** @var ISearchablePainting */
        $first = $this->first();
        return $first->getFilterInfoCategoryItems($categoryId);
    }

    public function getFilterInfoItems(): array
    {
        /** @var ISearchablePainting */
        $first = $this->first();
        return $first->getFilterInfoItems();
    }

    public function addInvolvedPersonsFullname(string $fullname): void
    {
        /** @var ISearchablePainting */
        foreach ($this as $searchablePainting) {
            $searchablePainting->addInvolvedPersonsFullname($fullname);
        }
    }

    public function getInvolvedPersonsFullnames(): array
    {
        /** @var ISearchablePainting */
        $first = $this->first();
        return $first->getInvolvedPersonsFullnames();
    }

    public function getFilterDating(): int
    {
        /** @var ISearchablePainting */
        $first = $this->first();
        return $first->getFilterDating();
    }

    public function setFilterDating(int $dating): void
    {
        /** @var ISearchablePainting */
        foreach ($this as $searchablePainting) {
            $searchablePainting->setFilterDating($dating);
        }
    }
}
