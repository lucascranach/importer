<?php

namespace CranachDigitalArchive\Importer\Modules\Filters\Loaders\Memory;

use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\Filter;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\CustomFilter;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\LangFilterContainer;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\CustomFiltersMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\ThesaurusTerm;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ReducedThesaurusMemoryExporter;

/**
 * CustomFilters and Thesaurus loader on a memory base
 */
class CustomFiltersAndThesaurusLoader extends Producer
{
    private $supportedLangs = [Language::DE, Language::EN];
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
            $langFilters = self::mapCustomFilterToLangSeperatedFilters($item, $this->supportedLangs);

            foreach ($langFilters as $lang => $filter) {
                $container = new LangFilterContainer($lang, $filter);
                $this->next($container);
            }
        }

        foreach ($this->thesaurusMemory->getData()->getRootTerms() as $term) {
            $langFilters = self::mapThesaurusTermToLangSeperatedFilters($term, $this->supportedLangs);

            foreach ($langFilters as $lang => $filter) {
                $container = new LangFilterContainer($lang, $filter);
                $this->next($container);
            }
        }

        /* Signaling that we are done reading the memory exporter */
        $this->notifyDone();
    }


    private static function mapCustomFilterToLangSeperatedFilters(CustomFilter $customFilter, array $langs): array
    {
        $newLangFilters = array_reduce($langs, function ($acc, $lang) use ($customFilter) {
            $newFilter = new Filter();

            $text = $customFilter->getLangText($lang);

            $newFilter->setId($customFilter->getId());
            $newFilter->setText(!is_null($text) ? $text: '');

            $acc[$lang] = $newFilter;
            return $acc;
        }, []);

        foreach ($customFilter->getChildren() as $child) {
            $childrenLangFilters = self::mapCustomFilterToLangSeperatedFilters($child, $langs);

            foreach ($childrenLangFilters as $lang => $children) {
                $newLangFilters[$lang]->addChild($children);
            }
        }

        return $newLangFilters;
    }


    private static function mapThesaurusTermToLangSeperatedFilters(ThesaurusTerm $term, array $langs): array
    {
        $newLangFilters = array_reduce($langs, function ($acc, $lang) use ($term) {
            $newFilter = new Filter();

            $id = $term->getAlt(ThesaurusTerm::ALT_DKULT_TERM_IDENTIFIER);

            if (!is_null($id)) {
                $newFilter->setId($id);
            }

            switch ($lang) {
                case Language::DE:
                    $newFilter->setText($term->getTerm());
                    break;
                case Language::EN:
                    $enBritishText = $term->getAlt(ThesaurusTerm::ALT_BRITISH_EQUIVALENT);
                    $enAlternativeText = $term->getAlt(ThesaurusTerm::ALT_ALTERNATIVE_TERM);

                    $enTexts = array_filter([$enBritishText, $enAlternativeText]);
                    $enText = array_shift($enTexts);

                    $newFilter->setText((!is_null($enText)) ? $enText : '');
                    break;
            }

            $acc[$lang] = $newFilter;
            return $acc;
        }, []);

        foreach ($term->getSubTerms() as $subTerm) {
            $childrenLangFilters = self::mapThesaurusTermToLangSeperatedFilters($subTerm, $langs);

            foreach ($childrenLangFilters as $lang => $children) {
                $newLangFilters[$lang]->addChild($children);
            }
        }

        return $newLangFilters;
    }
}
