<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search\SearchablePainting;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\CustomFiltersMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Person;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithBasicFilterValues extends Hybrid
{
    const ATTRIBUTION = 'attribution';
    const COLLECTION_REPOSITORY = 'collection_repository';
    const EXAMINATION_ANALYSIS = 'examination_analysis';


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
        if (!($item instanceof SearchablePainting)) {
            throw new Error('Pushed item is not of expected class \'SearchablePainting\'');
        }

        $this->extendWithBasicFilterValues($item);

        $this->next($item);
        return true;
    }


    private function extendWithBasicFilterValues(SearchablePainting $item): void
    {
        $basicFilters = [
            self::ATTRIBUTION => [],
            self::COLLECTION_REPOSITORY => [],
            self::EXAMINATION_ANALYSIS => [],
        ];

        $this->extendBasicFiltersForAttribution($item, $basicFilters);
        $this->extendBasicFiltersForCollectionAndRepository($item, $basicFilters);
        $this->extendBasicFiltersForExaminationAnalysis($item, $basicFilters);

        $item->addBasicFilters($basicFilters);
    }


    private function extendBasicFiltersForAttribution(
        SearchablePainting $item,
        array &$basicFilters
    ):void {
        $metadata = $item->getMetadata();
        if (is_null($metadata)) {
            return;
        }

        $langCode = $metadata->getLangCode();

        $attributionCheckItems = array_filter(
            $this->filters[self::ATTRIBUTION],
            function ($item) {
                return $item->hasFilters();
            },
        );

        foreach ($item->getPersons() as $person) {
            foreach ($attributionCheckItems as $checkItem) {
                foreach ($checkItem->getFilters() as $matchFilterRule) {
                    if ($this->matchesAttributionFilterRule($person, $matchFilterRule, $langCode)) {
                        self::addBasicFilter($basicFilters, $checkItem->getId());
                    }
                }
            }
        }
    }


    private function matchesAttributionFilterRule(Person $person, array $matchFilterRule, string $langCode): bool
    {
        $isAMatch = false;

        if (isset($matchFilterRule['name']) && isset($matchFilterRule['name'][$langCode])) {
            $isAMatch = $this->matchesFieldValue(
                $matchFilterRule['name'][$langCode],
                $person->getName(),
            );
        }

        if (isset($matchFilterRule['suffix']) && isset($matchFilterRule['suffix'][$langCode])) {
            $isAMatch = $this->matchesFieldValue(
                $matchFilterRule['suffix'][$langCode],
                $person->getSuffix(),
            );
        }

        if (isset($matchFilterRule['prefix']) && isset($matchFilterRule['prefix'][$langCode])) {
            $isAMatch = $this->matchesFieldValue(
                $matchFilterRule['prefix'][$langCode],
                $person->getPrefix(),
            );
        }

        return $isAMatch;
    }


    private function matchesFieldValue($ruleValue, $value): bool
    {
        if (empty($ruleValue)) {
            return empty($value);
        }

        return !!preg_match($ruleValue, $value);
    }


    private function extendBasicFiltersForCollectionAndRepository(
        SearchablePainting $item,
        array &$basicFilters
    ):void {
        $collectionAndRepositoryCheckItems = array_filter(
            $this->filters[self::COLLECTION_REPOSITORY],
            function ($item) {
                return $item->hasFilters();
            },
        );

        foreach ($collectionAndRepositoryCheckItems as $checkItem) {
            foreach ($checkItem->getFilters() as $matchFilterRule) {
                if (!isset($matchFilterRule['collection_repository'])) {
                    continue;
                }

                $regExp = $matchFilterRule['collection_repository'];

                $matchingRepository = !!preg_match($regExp, $item->getRepository());
                $matchingOwner = !!preg_match($regExp, $item->getOwner());

                if ($matchingRepository || $matchingOwner) {
                    self::addBasicFilter($basicFilters, $checkItem->getId());
                }
            }
        }
    }


    private function extendBasicFiltersForExaminationAnalysis(
        SearchablePainting $item,
        array &$basicFilters
    ):void {
        $metadata = $item->getMetadata();
        if (is_null($metadata)) {
            return;
        }

        $langCode = $metadata->getLangCode();

        $examinationAnalysisCheckItems = array_filter(
            $this->filters[self::EXAMINATION_ANALYSIS],
            function ($item) {
                return $item->hasFilters();
            },
        );

        $keywords = array_reduce(
            $item->getRestorationSurveys(),
            function ($acc, $survey) {
                return array_reduce(
                    $survey->getTests(),
                    function ($testAcc, $test) {
                        foreach ($test->getKeywords() as $keyword) {
                            $testAcc[] = $keyword->getName();
                        }

                        return $testAcc;
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

                    if (!!preg_match($regExp, $keyword)) {
                        self::addBasicFilter($basicFilters, $checkItem->getId());
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
                    $filters[self::ATTRIBUTION] = self::flattenFilterItemHierarchy($item);
                    break;

                case self::COLLECTION_REPOSITORY:
                    $filters[self::COLLECTION_REPOSITORY] = self::flattenFilterItemHierarchy($item);
                    break;

                case self::EXAMINATION_ANALYSIS:
                    $filters[self::EXAMINATION_ANALYSIS] = self::flattenFilterItemHierarchy($item);
                    break;

                default:
                    echo 'Unknown filter category: ' . $item->getId() . "\n";
            }
        }

        if (!isset($filters[self::ATTRIBUTION])) {
            throw new Error('Missing custom attribution filters!');
        }

        if (!isset($filters[self::COLLECTION_REPOSITORY])) {
            throw new Error('Missing custom collection repository filters!');
        }

        if (!isset($filters[self::EXAMINATION_ANALYSIS])) {
            throw new Error('Missing custom examination analysis filters!');
        }

        return $filters;
    }


    private static function flattenFilterItemHierarchy($item): array
    {
        $arr = [];

        foreach ($item->getChildren() as $childItem) {
            $subArr = self::flattenFilterItemHierarchy($childItem);

            $arr = array_merge($arr, $subArr);
        }

        array_unshift($arr, $item);

        return $arr;
    }


    private static function addBasicFilter(array &$basicFilters, string $filterId)
    {
        $splitFilterId = explode('.', $filterId, 2);

        if (count($splitFilterId) !== 2) {
            return;
        }

        list($category, $remainingFilterId) = $splitFilterId;

        if (!isset($basicFilters[$category])) {
            $basicFilters[$category] = [];
        }

        if (!in_array($remainingFilterId, $basicFilters[$category], true)) {
            $basicFilters[$category][] = $remainingFilterId;
        }
    }
}
