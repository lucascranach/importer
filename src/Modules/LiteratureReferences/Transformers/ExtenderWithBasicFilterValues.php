<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Filters\Exporters\CustomFiltersMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\CustomFilter;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Publication;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Search\FilterInfoItem;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Interfaces\ISearchableLiteratureReference;

class ExtenderWithBasicFilterValues extends Hybrid
{
    const MEDIA_TYPE = 'media_type';


    private $filters = null;

    private $objectsWithoutFilterConnections = [];


    private function __construct()
    {
        $this->objectsWithoutFilterConnections = [
            self::MEDIA_TYPE => [],
        ];
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
        if (!($item instanceof ISearchableLiteratureReference)) {
            throw new Error('Pushed item is not of expected interface \'ISearchableLiteratureReference\'');
        }

        $this->extendCollectionWithBasicFilterValues($item);

        $this->next($item);
        return true;
    }


    public function done(IProducer $producer)
    {
        parent::done($producer);

        echo "== LiteratureReference objects with missing filter connections: \n";

        foreach ([self::MEDIA_TYPE] as $type) {
            echo "=== " . $type . "\n";
            echo json_encode($this->objectsWithoutFilterConnections[$type], JSON_UNESCAPED_UNICODE);
            echo "\n\n";
        }
    }


    private function extendCollectionWithBasicFilterValues(ISearchableLiteratureReference $collection): void
    {
        /** @var ISearchableLiteratureReference $item */
        foreach ($collection as $item) {
            $this->extendBasicFiltersForPublication($item);
        }
    }


    private function extendBasicFiltersForPublication(ISearchableLiteratureReference $item):void
    {
        $metadata = $item->getMetadata();
        if (is_null($metadata)) {
            return;
        }

        $langCode = $metadata->getLangCode();
        $basicFilterInfos = [];

        $mediaTypeCheckItems = array_filter(
            $this->filters[self::MEDIA_TYPE],
            function ($item) {
                return $item->hasFilters();
            },
        );

        /** @var Publication */
        foreach ($item->getPublications() as $publication) {
            foreach ($mediaTypeCheckItems as $checkItem) {
                foreach ($checkItem->getFilters() as $matchFilterRule) {
                    if ($this->matchesPublicationFilterRule($publication, $matchFilterRule)) {
                        self::addBasicFilter($basicFilterInfos, $checkItem, $langCode);
                        self::addAncestorsBasicFilter(
                            $basicFilterInfos,
                            $checkItem,
                            $this->filters[self::MEDIA_TYPE],
                            $langCode
                        );
                    }
                }
            }
        }

        if (empty($basicFilterInfos)) {
            $this->keepTrackOfMissingFilterConnection(self::MEDIA_TYPE, $metadata->getId());
        }

        $item->addFilterInfoCategoryItems(self::MEDIA_TYPE, $basicFilterInfos);
    }


    private function matchesPublicationFilterRule(Publication $publication, array $matchFilterRule): bool
    {
        $givenRuleParts = 0;
        $matchingRuleParts = 0;

        if (isset($matchFilterRule['publication'])) {
            $givenRuleParts += 1;
            if ($matchFilterRule['publication'] === $publication->getType()) {
                $matchingRuleParts += 1;
            }
        }

        return $givenRuleParts > 0 && $givenRuleParts === $matchingRuleParts;
    }

    private function keepTrackOfMissingFilterConnection(string $type, string $id)
    {
        if (!in_array($id, $this->objectsWithoutFilterConnections[$type], true)) {
            $this->objectsWithoutFilterConnections[$type][] = $id;
        }
    }


    private static function prepareFilterItems(array $items)
    {
        $filters = [];
        $filterItemKindsToPrepare = [
            self::MEDIA_TYPE,
        ];

        foreach ($items as $item) {
            if (in_array($item->getId(), $filterItemKindsToPrepare, true)) {
                $filters[$item->getId()] = self::flattenFilterItemHierarchy($item);
            } else {
                echo 'Unknown filter category: ' . $item->getId() . "\n";
            }
        }

        if (!isset($filters[self::MEDIA_TYPE])) {
            throw new Error('Missing custom media type filters!');
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
