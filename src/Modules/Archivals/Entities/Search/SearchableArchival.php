<?php


namespace CranachDigitalArchive\Importer\Modules\Archivals\Entities\Search;

use CranachDigitalArchive\Importer\Modules\Archivals\Entities\Archival;

/**
 * Representing a single searchable artchival and all flattened and embedded related data
 *    One instance containing only data for one language
 */
class SearchableArchival extends Archival
{
    public $repositoryId = '';


    public function __construct()
    {
        parent::__construct();
    }

    public function setRepositoryId(string $repositoryId): void
    {
        $this->repositoryId = $repositoryId;
    }

    public function getRepositoryId(): string
    {
        return $this->repositoryId;
    }
}
