<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search;

use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Graphics\Interfaces\ISearchableGraphic;

class SearchableGraphicLanguageCollection extends GraphicLanguageCollection implements ISearchableGraphic
{
    protected function createItem(): ISearchableGraphic
    {
        return new SearchableGraphic();
    }

    public function addFilterInfoCategoryItems(string $categoryId, array $filterInfoItems): void
    {
        /** @var ISearchableGraphic */
        foreach ($this as $searchableGraphic) {
            $searchableGraphic->addFilterInfoCategoryItems($categoryId, $filterInfoItems);
        }
    }


    public function getFilterInfoCategoryItems(string $categoryId): ?array
    {
        /** @var ISearchableGraphic */
        $first = $this->first();
        return $first->getFilterInfoCategoryItems($categoryId);
    }


    public function getFilterInfoItems(): array
    {
        /** @var ISearchableGraphic */
        $first = $this->first();
        return $first->getFilterInfoItems();
    }


    public function addInvolvedPersonsFullname(string $fullname): void
    {
        /** @var ISearchableGraphic */
        foreach ($this as $searchableGraphic) {
            $searchableGraphic->addInvolvedPersonsFullname($fullname);
        }
    }


    public function getInvolvedPersonsFullnames(): array
    {
        /** @var ISearchableGraphic */
        $first = $this->first();
        return $first->getInvolvedPersonsFullnames();
    }


    public function getFilterDating(): int
    {
        /** @var ISearchableGraphic */
        $first = $this->first();
        return $first->getFilterDating();
    }


    public function setFilterDating(int $dating): void
    {
        /** @var ISearchableGraphic */
        foreach ($this as $searchableGraphic) {
            $searchableGraphic->setFilterDating($dating);
        }
    }


    public function getChildRepositories(): array
    {
        /** @var ISearchableGraphic */
        $first = $this->first();
        return $first->getChildRepositories();
    }


    public function setChildRepositories(array $childRepositories): void
    {
        /** @var ISearchableGraphic */
        foreach ($this as $searchableGraphic) {
            $searchableGraphic->setChildRepositories($childRepositories);
        }
    }

    public function addChildRepository(string $childRepository): void
    {
        /** @var ISearchableGraphic */
        foreach ($this as $searchableGraphic) {
            $searchableGraphic->addChildRepository($childRepository);
        }
    }
}
