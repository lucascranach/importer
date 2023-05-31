<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Search;

use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReferenceLanguageCollection;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces\ISearchableLiteratureReference;

class SearchableLiteratureReferenceLanguageCollection extends LiteratureReferenceLanguageCollection implements ISearchableLiteratureReference
{
    protected function createItem(): ISearchableLiteratureReference
    {
        return new SearchableLiteratureReference();
    }

    public function setPublicationsLine(string $publicationsLine): void
    {
        foreach ($this as $searchableLiteratureReference) {
            $searchableLiteratureReference->setPublicationsLine($publicationsLine);
        }
    }

    public function getPublicationsLine(): string
    {
        return $this->first()->getPublicationsLine();
    }
}
