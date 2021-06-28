<?php


namespace CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search;

use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;

/**
 * Representing a single searchable graphic and all flattened and embedded related data
 *    One instance containing only data for one language
 */
class SearchableGraphic extends Graphic
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
