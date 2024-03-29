<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\CustomFiltersMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\RestorationLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Restorations\Interfaces\IRestoration;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithIds extends Hybrid
{
    const ATTRIBUTION = 'attribution';
    const COLLECTION_REPOSITORY = 'collection_repository';
    const EXAMINATION_ANALYSIS = 'examination_analysis';
    const CATALOG = 'catalog';


    private $filters = null;


    private function __construct()
    {
    }


    public static function new(CustomFiltersMemoryExporter $memoryExporter): self
    {
        $transformer = new self;

        $customFiltersFromMemory = $memoryExporter->getData();

        $transformer->filters = !is_null($customFiltersFromMemory)
            ? self::prepareFilterItems($customFiltersFromMemory)
            : [];
        return $transformer;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof RestorationLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'RestorationLanguageCollection\'');
        }

        foreach ($item as $langCode => $restoration) {
            $this->extendWithBasicFilterValues($langCode, $restoration);
        }

        $this->next($item);
        return true;
    }


    private function extendWithBasicFilterValues(string $langCode, IRestoration $item): void
    {
        $this->extendWithExaminationAnalysisIds($langCode, $item);
    }


    private function extendWithExaminationAnalysisIds(string $langCode, IRestoration $item):void
    {
        $examinationAnalysisCheckItems = array_filter(
            $this->filters[self::EXAMINATION_ANALYSIS],
            function ($item) {
                return $item->hasFilters();
            },
        );

        $keywords = array_reduce(
            $item->getSurveys(),
            function ($acc, $survey) {
                return array_reduce(
                    $survey->getTests(),
                    function ($testAcc, $test) {
                        return array_merge($testAcc, $test->getKeywords());
                    },
                    $acc,
                );
            },
            [],
        );

        foreach ($keywords as $keyword) {
            foreach ($examinationAnalysisCheckItems as $checkItem) {
                foreach ($checkItem->getFilters() as $matchFilterRule) {
                    if (!isset($matchFilterRule['keyword'])
                        || !isset($matchFilterRule['keyword'][$langCode])) {
                        continue;
                    }

                    $regExp = $matchFilterRule['keyword'][$langCode];

                    if (!!preg_match($regExp, $keyword->getName())) {
                        $keyword->setId($checkItem->getId());
                    }
                }
            }
        }
    }


    private static function prepareFilterItems(array $items)
    {
        $filters = [];

        foreach ($items as $item) {
            switch ($item->getId()) {
                case self::ATTRIBUTION:
                case self::COLLECTION_REPOSITORY:
                case self::CATALOG:
                    // Skipped because of its only use in the paintings id extender
                    break;

                case self::EXAMINATION_ANALYSIS:
                    $filters[self::EXAMINATION_ANALYSIS] = self::flattenFilterItemHierarchy($item);
                    break;

                default:
                    echo 'Unknown filter category: ' . $item->getId() . "\n";
            }
        }

        if (!isset($filters[self::EXAMINATION_ANALYSIS])) {
            throw new Error('Missing custom examination analysis filters!');
        }

        return $filters;
    }


    private static function flattenFilterItemHierarchy($item): array
    {
        $arr = [
            $item->getId() => $item,
        ];

        foreach ($item->getChildren() as $childItem) {
            $subArr = self::flattenFilterItemHierarchy($childItem);

            $arr = array_merge($arr, $subArr);
        }

        return $arr;
    }
}
