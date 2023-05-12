<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Entities\Search;

use CranachDigitalArchive\Importer\Modules\Archivals\Entities\ArchivalLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\Search\SearchableArchival;
use CranachDigitalArchive\Importer\Modules\Archivals\Interfaces\ISearchableArchival;

class SearchableArchivalLanguageCollection extends ArchivalLanguageCollection implements ISearchableArchival
{
    protected function createItem(): ISearchableArchival
    {
        return new SearchableArchival();
    }

    public function setRepositoryId(string $repositoryId): void
    {
        /** @var ISearchableArchival */
        foreach ($this as $archival) {
            $archival->setRepositoryId($repositoryId);
        }
    }

    public function getRepositoryId(): string
    {
        return $this->first()->getRepositoryId();
    }
}
