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
    public $involvedPersonsFullnames = [];
    public $filterDating = 0;
    public $childRepositories = [];


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


    public function getFilterDating(): int
    {
        return $this->filterDating;
    }


    public function setFilterDating(int $dating): void
    {
        $this->filterDating = $dating;
    }


    public function getChildRepositories(): array
    {
        return $this->childRepositories;
    }


    public function setChildRepositories(array $childRepositories): void
    {
        $this->childRepositories = $childRepositories;
    }

    public function addChildRepository(string $childRepository): void
    {
        $this->childRepositories[] = $childRepository;
    }
}
