<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Search;

use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReferenceLanguageCollection;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces\ISearchableLiteratureReference;

class SearchableLiteratureReferenceLanguageCollection extends LiteratureReferenceLanguageCollection implements ISearchableLiteratureReference
{
    protected function createItem(): ISearchableLiteratureReference
    {
        return new SearchableLiteratureReference();
    }

    public function addFilterInfoCategoryItems(string $categoryId, array $filterInfoItems): void
    {
        /** @var ISearchableLiteratureReference */
        foreach ($this as $searchablePainting) {
            $searchablePainting->addFilterInfoCategoryItems($categoryId, $filterInfoItems);
        }
    }

    public function getFilterInfoCategoryItems(string $categoryId): ?array
    {
        /** @var ISearchableLiteratureReference */
        $first = $this->first();
        return $first->getFilterInfoCategoryItems($categoryId);
    }

    public function getFilterInfoItems(): array
    {
        /** @var ISearchableLiteratureReference */
        $first = $this->first();
        return $first->getFilterInfoItems();
    }

    public function setPublicationsLine(string $publicationsLine): void
    {
        foreach ($this as $searchableLiteratureReference) {
            $searchableLiteratureReference->setPublicationsLine($publicationsLine);
        }
    }

    public function getPublicationsLine(): string
    {
        return $this->first()->getPublicationsLine();
    }
}
