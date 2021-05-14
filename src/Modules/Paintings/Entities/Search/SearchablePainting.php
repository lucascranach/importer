<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search;

use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Painting;

/**
 * Representing a single searchable painting and all flattened and embedded related data
 * 	One instance containing only data for one language
 */
class SearchablePainting extends Painting
{
    public $basicFilters = [];
    public $thesaurus = [];


    public function __construct()
    {
        parent::__construct();
    }


    public function addBasicFilters(array $basicFilters): void
    {
        $this->basicFilters = $basicFilters;
    }


    public function getBasicFilters(): array
    {
        return $this->basicFilters;
    }


    public function addThesaurusItems(array $thesaurusItems): void
    {
        $this->thesaurus = array_merge($this->thesaurus, $thesaurusItems);
    }


    public function getThesaurusItems(): array
    {
        return $this->thesaurus;
    }
}
