<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Transformers;

use CranachDigitalArchive\Importer\Modules\Archivals\Interfaces\ISearchableArchival;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\Search\SearchableArchivalLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use Error;

class ExtenderWithRepositoryId extends Hybrid
{
    private function __construct()
    {
    }


    public static function new(): self
    {
        return new self;
    }

    public function handleItem($item): bool
    {
        if (!($item instanceof SearchableArchivalLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'SearchableArchivalLanguageCollection\'');
        }

        /** @var ISearchableArchival */
        foreach ($item as $subItem) {
            // We need to handle the repository id generation for an archival in each language
            $repositoryId = self::getSlug($item->getRepository());
            $subItem->setRepositoryId($repositoryId);
        }

        $this->next($item);
        return true;
    }

    /**
     * @return void
     */
    public function done(IProducer $producer)
    {
        parent::done($producer);
    }

    private static function getSlug(string $value): string
    {
        // See: https://stackoverflow.com/a/26054862
        $value = preg_replace('/[^\p{L}\d]+/u', '-', $value);
        $value = trim($value, '-');
        $value = iconv('utf-8', 'ASCII//TRANSLIT', $value);
        $value = strtolower($value);
        $value = preg_replace('/[^-\w]+/', '', $value);
        return $value;
    }
}
