<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search;

use CranachDigitalArchive\Importer\AbstractItemLanguageCollection;

/**
 * @template-extends AbstractItemLanguageCollection<SearchablePainting>
 */
class SearchablePaintingLanguageCollection extends AbstractItemLanguageCollection
{
    protected function createItem(): SearchablePainting
    {
        return new SearchablePainting();
    }
}
