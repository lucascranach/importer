<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\CustomFiltersMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Person;
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\DrawingLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Drawings\Interfaces\IDrawing;
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
        if (!($item instanceof DrawingLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'DrawingLanguageCollection\'');
        }

        $this->extendCollectionWithBasicFilterValues($item);

        $this->next($item);
        return true;
    }


    private function extendCollectionWithBasicFilterValues(DrawingLanguageCollection $collection): void
    {
        /** @var string $langCode */
        /** @var IDrawing $item */
        foreach ($collection as $langCode => $item) {
            $this->extendWithAttributionIds($langCode, $item);
            $this->extendWithCollectionAndRepositoryIds($item);
        }
    }


    private function extendWithAttributionIds(string $langCode, IDrawing $item):void
    {
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
                        $person->setId($checkItem->getId());
                    }
                }
            }
        }
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


    private function extendWithCollectionAndRepositoryIds(IDrawing $item):void
    {
        $metadata = $item->getMetadata();
        if (is_null($metadata)) {
            return;
        }

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

                if ($matchingRepository) {
                    $item->setCollectionRepositoryId($checkItem->getId());
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

                case self::CATALOG:
                case self::EXAMINATION_ANALYSIS:
                    // Skipped because of its only use in the restoration id extender
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
