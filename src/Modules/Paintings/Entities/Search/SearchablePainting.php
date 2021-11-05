<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search;

use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Painting;

/**
 * Representing a single searchable painting and all flattened and embedded related data
 * 	One instance containing only data for one language
 */
class SearchablePainting extends Painting
{
    public $filterInfos = [];
    public $involvedPersonsFullnames = [];


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


    public function addInvolvedPersonsFullname(string $fullname): void
    {
        $this->involvedPersonsFullnames[] = $fullname;
    }


    public function getInvolvedPersonsFullnames(): array
    {
        return $this->involvedPersonsFullnames;
    }
}
