<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search\SearchablePainting;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithBasicFilterValues extends Hybrid
{
    const ATTRIBUTION = 'attribution';
    const DATING = 'dating';
    const COLLECTION_REPOSITORY = 'collection_repository';
    const EXAMINATION_ANALYSIS = 'examination_analysis';


    private $filters = null;


    private function __construct()
    {
    }


    public static function withCustomFilterDefinitionsAt(string $pathToFilterDefinitionsFile): self
    {
        $transformer = new self;

        if (!file_exists($pathToFilterDefinitionsFile)) {
            throw new Error('Custom filter definitions not found at: ' . $pathToFilterDefinitionsFile);
        }

        $rawFilters = json_decode(file_get_contents($pathToFilterDefinitionsFile), true);

        $transformer->filters = self::prepareFilterItems($rawFilters);

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
        $basicFilters = [];

        $this->extendBasicFiltersForAttribution($item, $basicFilters);
        $this->extendBasicFiltersForCollectionAndRepository($item, $basicFilters);

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
                return isset($item['filters']);
            },
        );

        foreach ($item->getPersons() as $person) {
            foreach ($attributionCheckItems as $attributionCheckItem) {
                foreach ($attributionCheckItem['filters'] as $matchFilterRule) {
                    if ($this->matchesAttributionFilterRule($person, $matchFilterRule, $langCode)) {
                        $basicFilters[$attributionCheckItem['id']] = true;
                    }
                }
            }
        }
    }


    private function matchesAttributionFilterRule($person, $matchFilterRule, $langCode): bool
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
                return isset($item['filters']);
            },
        );

        foreach ($collectionAndRepositoryCheckItems as $checkItem) {
            foreach ($checkItem['filters'] as $matchFilterRule) {
                if (!isset($matchFilterRule['collection_repository'])) {
                    continue;
                }

                $regExp = $matchFilterRule['collection_repository'];

                $matchingRepository = !!preg_match($regExp, $item->getRepository());
                $matchingOwner = !!preg_match($regExp, $item->getOwner());

                if ($matchingRepository || $matchingOwner) {
                    $basicFilters[$checkItem['id']] = true;
                }
            }
        }
    }


    private static function prepareFilterItems(array $items)
    {
        $filters = [];

        foreach ($items as $item) {
            switch ($item['id']) {
                case self::ATTRIBUTION:
                    $filters[self::ATTRIBUTION] = self::flattenFilterItemHierarchy($item);
                    break;

                case self::DATING:
                    $filters[self::DATING] = self::flattenFilterItemHierarchy($item);
                    break;

                case self::COLLECTION_REPOSITORY:
                    $filters[self::COLLECTION_REPOSITORY] = self::flattenFilterItemHierarchy($item);
                    break;

                case self::EXAMINATION_ANALYSIS:
                    $filters[self::EXAMINATION_ANALYSIS] = self::flattenFilterItemHierarchy($item);
                    break;

                default:
                    echo 'Unknown filter category: ' . $item['id'] . "\n";
            }
        }

        if (!isset($filters[self::ATTRIBUTION])) {
            throw new Error('Missing custom attribution filters!');
        }

        if (!isset($filters[self::DATING])) {
            throw new Error('Missing custom dating filters!');
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

        if (isset($item['children'])) {
            foreach ($item['children'] as $childItem) {
                $subArr = self::flattenFilterItemHierarchy($childItem);

                $arr = array_merge($arr, $subArr);
            }
            unset($item['children']);
        }

        array_unshift($arr, $item);

        return $arr;
    }
}
