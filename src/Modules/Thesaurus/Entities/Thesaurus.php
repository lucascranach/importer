<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus\Entities;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;

/**
 * Representing a single thesaurus hierachy
 */
class Thesaurus implements IBaseItem
{
    public $rootTerms = [];


    public function __construct()
    {
    }


    public function addRootTerm(ThesaurusTerm $term)
    {
        $this->rootTerms[] = $term;
    }


    public function getRootTerms(): array
    {
        return $this->rootTerms;
    }
}
