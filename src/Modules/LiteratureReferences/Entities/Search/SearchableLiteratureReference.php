<?php


namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Search;

use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReference;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces\ISearchableLiteratureReference;

/**
 * Representing a single searchable literature reference and all flattened and embedded related data
 *    One instance containing only data for one language
 */
class SearchableLiteratureReference extends LiteratureReference implements ISearchableLiteratureReference
{
    public $filterInfos = [];
    public $publicationsLine = '';


    public function __construct()
    {
        parent::__construct();
    }

    public function addFilterInfoCategoryItems(string $categoryId, array $filterInfoItems): void
    {
        if (!isset($this->filterInfos[$categoryId])) {
            $this->filterInfos[$categoryId] = [];
        }

        $this->filterInfos[$categoryId] = array_merge(
            $this->filterInfos[$categoryId],
            $filterInfoItems
        );
    }

    public function getFilterInfoCategoryItems(string $categoryId): ?array
    {
        if (!isset($this->filterInfos[$categoryId])) {
            return null;
        }

        return $this->filterInfos[$categoryId];
    }

    public function getFilterInfoItems(): array
    {
        return $this->filterInfos;
    }

    public function setPublicationsLine(string $publicationsLine): void
    {
        $this->publicationsLine = $publicationsLine;
    }

    public function getPublicationsLine(): string
    {
        return $this->publicationsLine;
    }
}
