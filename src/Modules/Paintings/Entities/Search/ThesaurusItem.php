<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search;

/**
 * Representing a single searchable graphic and all flattened and embedded related data
 * 	One instance containing only data for one language
 */
class ThesaurusItem
{
    public $id = null;
    public $parentId = null;
    public $term = '';


    public function __construct()
    {
    }


    public function setId(string $id)
    {
        $this->id = $id;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function setParentId(string $parentId)
    {
        $this->parentId = $parentId;
    }


    public function getParentId(): ?string
    {
        return $this->parentId;
    }


    public function setTerm(string $term)
    {
        $this->term = $term;
    }


    public function getTerm(): string
    {
        return $this->term;
    }
}
