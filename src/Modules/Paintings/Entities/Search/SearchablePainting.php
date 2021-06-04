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


    public function __construct()
    {
        parent::__construct();
    }


    public function addFilterInfoItems(array $filterInfoItems): void
    {
        $this->filterInfos = array_merge($this->filterInfos, $filterInfoItems);
    }


    public function getFilterInfoItems(): array
    {
        return $this->filterInfos;
    }
}
