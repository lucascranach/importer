<?php


namespace CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search;

use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;

/**
 * Representing a single searchable graphic and all flattened and embedded related data
 *    One instance containing only data for one language
 */
class SearchableGraphic extends Graphic
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
