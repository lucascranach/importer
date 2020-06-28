<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search;

use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Painting;

/**
 * Representing a single searchable graphic and all flattened and embedded related data
 * 	One instance containing only data for one language
 */
class SearchablePainting extends Painting
{
    public $thesaurus = [];


    public function __construct()
    {
        parent::__construct();
    }


    public function addThesaurusItems(array $thesaurusItems)
    {
        $this->thesaurus = array_merge($this->thesaurus, $thesaurusItems);
    }


    public function getThesaurusItems(): array
    {
        return $this->thesaurus;
    }
}
