<?php

namespace CranachDigitalArchive\Importer\Modules\Filters\Loaders\Memory;

use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\Filter;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\CustomFilter;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\LangFilterContainer;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\CustomFiltersMemoryExporter;

/**
 * CustomFilters and Thesaurus loader on a memory base
 */
class CustomFiltersLoader extends Producer
{
    private $supportedLangs = [Language::DE, Language::EN];
    private $customFiltersMemory = null;

    private function __construct()
    {
    }


    /**
     * @return self
     */
    public static function withMemory(
        CustomFiltersMemoryExporter $customFiltersMemory,
    ) {
        $loader = new self;
        $loader->customFiltersMemory = $customFiltersMemory;

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
}
