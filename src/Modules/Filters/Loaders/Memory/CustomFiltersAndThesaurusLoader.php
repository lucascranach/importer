<?php

namespace CranachDigitalArchive\Importer\Modules\Filters\Loaders\Memory;

use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\Filter;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\CustomFilter;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\CustomFiltersMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\ThesaurusTerm;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ReducedThesaurusMemoryExporter;

/**
 * CustomFilters and Thesaurus loader on a memory base
 */
class CustomFiltersAndThesaurusLoader extends Producer
{
    private $customFiltersMemory = null;
    private $thesaurusMemory = null;

    private function __construct()
    {
    }


    /**
     * @return self
     */
    public static function withMemory(
        CustomFiltersMemoryExporter $customFiltersMemory,
        ReducedThesaurusMemoryExporter $thesaurusMemory,
    ) {
        $loader = new self;
        $loader->customFiltersMemory = $customFiltersMemory;
        $loader->thesaurusMemory = $thesaurusMemory;

        return $loader;
    }


    /**
     * @return void
     */
    public function run()
    {
        echo "Processing memory custom filters\n";

        foreach ($this->customFiltersMemory->getData() as $item) {
            $this->next(self::mapCustomFilterToFilter($item));
        }

        foreach ($this->thesaurusMemory->getData()->getRootTerms() as $term) {
            $this->next(self::mapThesaurusTermToFilter($term));
        }

        /* Signaling that we are done reading the memory exporter */
        $this->notifyDone();
    }


    private static function mapCustomFilterToFilter(CustomFilter $customFilter): Filter
    {
        $newFilter = new Filter();

        $newFilter->setId($customFilter->getId());
        $newFilter->setText($customFilter->getText());

        foreach ($customFilter->getChildren() as $child) {
            $newFilter->addChild(self::mapCustomFilterToFilter($child));
        }

        return $newFilter;
    }


    private static function mapThesaurusTermToFilter(ThesaurusTerm $term): Filter
    {
        $newFilter = new Filter();

        $id = $term->getAlt(ThesaurusTerm::ALT_DKULT_TERM_IDENTIFIER);

        if (!is_null($id)) {
            $newFilter->setId($id);
        }

        $enText = $term->getAlt(ThesaurusTerm::ALT_BRITISH_EQUIVALENT);

        $text = [
            Language::DE => $term->getTerm(),
            Language::EN => !is_null($enText) ? $enText : '',
        ];

        $newFilter->setText($text);

        foreach ($term->getSubTerms() as $subTerm) {
            $newFilter->addChild(self::mapThesaurusTermToFilter($subTerm));
        }

        return $newFilter;
    }
}
