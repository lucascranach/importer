<?php


namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Search;

use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReference;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces\ISearchableLiteratureReference;

/**
 * Representing a single searchable literature reference and all flattened and embedded related data
 *    One instance containing only data for one language
 */
class SearchableLiteratureReference extends LiteratureReference implements ISearchableLiteratureReference
{
    public $publicationsLine = '';


    public function __construct()
    {
        parent::__construct();
    }

    public function setPublicationsLine(string $publicationsLine): void
    {
        $this->publicationsLine = $publicationsLine;
    }

    public function getPublicationsLine(): string
    {
        return $this->publicationsLine;
    }
}
