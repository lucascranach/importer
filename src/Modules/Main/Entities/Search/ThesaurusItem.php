<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities\Search;

/**
 * Representing a single searchable thesaurus item
 */
class ThesaurusItem
{
    public $id = null;
    public $parentId = null;
    public $term = '';


    public function __construct()
    {
    }


    public function setId(string $id): void
    {
        $this->id = $id;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function setParentId(string $parentId): void
    {
        $this->parentId = $parentId;
    }


    public function getParentId(): ?string
    {
        return $this->parentId;
    }


    public function setTerm(string $term): void
    {
        $this->term = $term;
    }


    public function getTerm(): string
    {
        return $this->term;
    }
}
