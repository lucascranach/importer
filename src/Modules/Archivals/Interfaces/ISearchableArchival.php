<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Interfaces;

interface ISearchableArchival extends IArchival
{
    public function setRepositoryId(string $repositoryId): void;

    public function getRepositoryId(): string;
}
