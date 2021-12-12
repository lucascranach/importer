<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search\SearchableGraphic;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\CustomFiltersMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\CustomFilter;
use CranachDigitalArchive\Importer\Modules\Main\Entities\CatalogWorkReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Person;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Search\FilterInfoItem;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithBasicFilterValues extends Hybrid
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
        if (!($item instanceof SearchableGraphic)) {
            throw new Error('Pushed item is not of expected class \'SearchableGraphic\'');
        }

        $this->extendWithBasicFilterValues($item);

        $this->next($item);
        return true;
    }


    private function extendWithBasicFilterValues(SearchableGraphic $item): void
    {
        $this->extendBasicFiltersForAttribution($item);
        $this->extendBasicFiltersForCollectionAndRepository($item);
        $this->extendBasicFiltersForExaminationAnalysis($item);
        $this->extendBasicFiltersForAssocation($item);
    }


    private function extendBasicFiltersForAttribution(SearchableGraphic $item):void
    {
        $metadata = $item->getMetadata();
        if (is_null($metadata)) {
            return;
        }

        $langCode = $metadata->getLangCode();
        $basicFilterInfos = [];

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
                        self::addBasicFilter($basicFilterInfos, $checkItem, $langCode, $person->getDisplayOrder());
                        self::addAncestorsBasicFilter(
                            $basicFilterInfos,
                            $checkItem,
                            $this->filters[self::ATTRIBUTION],
                            $langCode
                        );
                    }
                }
            }
        }

        $item->addFilterInfoCategoryItems(self::ATTRIBUTION, $basicFilterInfos);
    }


    private function matchesAttributionFilterRule(Person $person, array $matchFilterRule, string $langCode): bool
    {
        $givenRuleParts = 0;
        $matchingRuleParts = 0;

        if (isset($matchFilterRule['name']) && isset($matchFilterRule['name'][$langCode])) {
            $givenRuleParts += 1;
            if ($this->matchesFieldValue(
                $matchFilterRule['name'][$langCode],
                $person->getName(),
            )) {
                $matchingRuleParts += 1;
            }
        }

        if (isset($matchFilterRule['suffix']) && isset($matchFilterRule['suffix'][$langCode])) {
            $givenRuleParts += 1;
            if ($this->matchesFieldValue(
                $matchFilterRule['suffix'][$langCode],
                $person->getSuffix(),
            )) {
                $matchingRuleParts += 1;
            }
        }

        if (isset($matchFilterRule['prefix']) && isset($matchFilterRule['prefix'][$langCode])) {
            $givenRuleParts += 1;
            if ($this->matchesFieldValue(
                $matchFilterRule['prefix'][$langCode],
                $person->getPrefix(),
            )) {
                $matchingRuleParts += 1;
            }
        }

        return $givenRuleParts > 0 && $givenRuleParts === $matchingRuleParts;
    }


    private function matchesFieldValue($ruleValue, $value): bool
    {
        if (empty($ruleValue)) {
            return empty($value);
        }

        return !!preg_match($ruleValue, $value);
    }


    private function extendBasicFiltersForCollectionAndRepository(SearchableGraphic $item):void
    {
        $metadata = $item->getMetadata();
        if (is_null($metadata)) {
            return;
        }

        $langCode = $metadata->getLangCode();
        $basicFilterInfos = [];

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
                    self::addBasicFilter($basicFilterInfos, $checkItem, $langCode);
                    self::addAncestorsBasicFilter(
                        $basicFilterInfos,
                        $checkItem,
                        $this->filters[self::COLLECTION_REPOSITORY],
                        $langCode,
                    );
                }
            }
        }

        $item->addFilterInfoCategoryItems(self::COLLECTION_REPOSITORY, $basicFilterInfos);
    }


    private function extendBasicFiltersForExaminationAnalysis(SearchableGraphic $item):void
    {
        $metadata = $item->getMetadata();
        if (is_null($metadata)) {
            return;
        }

        $langCode = $metadata->getLangCode();
        $basicFilterInfos = [];

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
                        self::addBasicFilter($basicFilterInfos, $checkItem, $langCode);
                        self::addAncestorsBasicFilter(
                            $basicFilterInfos,
                            $checkItem,
                            $this->filters[self::EXAMINATION_ANALYSIS],
                            $langCode,
                        );
                    }
                }
            }
        }

        $item->addFilterInfoCategoryItems(self::EXAMINATION_ANALYSIS, $basicFilterInfos);
    }


    private function extendBasicFiltersForAssocation(SearchableGraphic $item):void
    {
        $metadata = $item->getMetadata();
        if (is_null($metadata)) {
            return;
        }

        $langCode = $metadata->getLangCode();
        $basicFilterInfos = [];

        $catalogCheckItems = array_filter(
            $this->filters[self::CATALOG],
            function ($item) {
                return $item->hasFilters();
            },
        );

        foreach ($item->getCatalogWorkReferences() as $catalogWorkReference) {
            foreach ($catalogCheckItems as $checkItem) {
                foreach ($checkItem->getFilters() as $matchFilterRule) {
                    if ($this->matchesCatalogWorkReferenceFilterRule($catalogWorkReference, $matchFilterRule, $langCode)) {
                        self::addBasicFilter($basicFilterInfos, $checkItem, $langCode);
                        self::addAncestorsBasicFilter(
                            $basicFilterInfos,
                            $checkItem,
                            $this->filters[self::CATALOG],
                            $langCode,
                        );
                    }
                }
            }
        }

        $item->addFilterInfoCategoryItems(self::CATALOG, $basicFilterInfos);
    }


    private function matchesCatalogWorkReferenceFilterRule(CatalogWorkReference $catalogWorkReference, array $matchFilterRule, string $langCode): bool
    {
        if (!isset($matchFilterRule['description'])
            || !isset($matchFilterRule['description'][$langCode])
            || !isset($matchFilterRule['referenceNumber'])
            || !isset($matchFilterRule['referenceNumber'][$langCode])) {
            return false;
        }

        return preg_match($matchFilterRule['description'][$langCode], $catalogWorkReference->getDescription())
            && preg_match($matchFilterRule['referenceNumber'][$langCode], $catalogWorkReference->getReferenceNumber());
    }


    private static function prepareFilterItems(array $items)
    {
        $filters = [];
        $filterItemKindsToPrepare = [
            self::ATTRIBUTION,
            self::COLLECTION_REPOSITORY,
            self::EXAMINATION_ANALYSIS,
            self::CATALOG,
        ];

        foreach ($items as $item) {
            if (in_array($item->getId(), $filterItemKindsToPrepare, true)) {
                $filters[$item->getId()] = self::flattenFilterItemHierarchy($item);
            } else {
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

        if (!isset($filters[self::CATALOG])) {
            throw new Error('Missing custom catalog filters!');
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


    private static function addBasicFilter(array &$basicFilterInfos, CustomFilter $checkItem, string $langCode, int $order = 0)
    {
        $id = $checkItem->getId();
        $text = $checkItem->getLangText($langCode);

        if (is_null($text)) {
            throw new Error('Missing localized text for: ' . $langCode);
        }

        if (!self::basicFilterInfoAlreadyExists($basicFilterInfos, $id)) {
            $newFilterInfo = new FilterInfoItem();
            $newFilterInfo->setId($id);
            $newFilterInfo->setText($text);
            $newFilterInfo->setParentId($checkItem->getParentId());

            if ($order > 0) {
                $newFilterInfo->setOrder($order);
            }

            $basicFilterInfos[] = $newFilterInfo;
        }
    }


    private static function addAncestorsBasicFilter(array &$basicFilterInfos, CustomFilter $checkItem, array $items, string $langCode)
    {
        $parentId = $checkItem->getParentId();

        if (is_null($parentId) || !isset($items[$parentId]) || is_null($items[$parentId]->getParentId())) {
            return;
        }

        $parentItem = $items[$parentId];

        self::addBasicFilter($basicFilterInfos, $parentItem, $langCode);
        self::addAncestorsBasicFilter($basicFilterInfos, $parentItem, $items, $langCode);
    }


    private static function basicFilterInfoAlreadyExists(array $basicFilterInfos, string $filterId)
    {
        $matchingFilterInfos = array_filter(
            $basicFilterInfos,
            function ($filterInfo) use ($filterId) {
                return $filterInfo->getId() === $filterId;
            },
        );

        return count($matchingFilterInfos) > 0;
    }
}