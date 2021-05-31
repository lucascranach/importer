<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IMemoryExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\Thesaurus;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Thesaurus in memory exporter
 */
class ThesaurusMemoryExporter extends Consumer implements IMemoryExporter
{
    const ALT_NAME_DKULT_TERM_IDENTIFIER = 'dkultTermIdentifier';

    private $item = null;
    private $done = false;


    private function __construct()
    {
    }


    public static function new(): self
    {
        return new self();
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof Thesaurus)) {
            throw new Error('Pushed item is not of expected class \'Thesaurus\'');
        }

        if ($this->done) {
            throw new \Error('Can\'t push more items after done() was called!');
        }

        $this->item = $item;

        $duplicateItemIds = $this->getDuplicatesItemIds($item);
        if (count($duplicateItemIds) > 0) {
            $this->outputDuplicateItemIds($duplicateItemIds);
        }

        $altFieldName = self::ALT_NAME_DKULT_TERM_IDENTIFIER;

        $itemsWithMissingAltField = $this->getItemsWithMissingAltField($item, $altFieldName);
        if (count($itemsWithMissingAltField) > 0) {
            $this->outputItemsWithMissingAltField($itemsWithMissingAltField, $altFieldName);
        }

        return true;
    }


    public function getData(): Thesaurus
    {
        if (!$this->isDone()) {
            throw new Error('Can not return thesaurus data if not done!');
        }

        return $this->item;
    }


    public function findByFields(array $fieldValues)
    {
        if (!$this->isDone()) {
            throw new Error('Can not return thesaurus data if not done!');
        }

        $items = !is_null($this->item) ? [$this->item] : [];

        foreach ($items as $item) {
            $matching = true;

            foreach ($fieldValues as $fieldName => $value) {
                $matching = $matching && isset($item->{$fieldName}) && $item->{$fieldName} === $value;
            }

            if ($matching) {
                return $item;
            }
        }

        return null;
    }


    private function getDuplicatesItemIds(Thesaurus $item): array
    {
        $rootTerms = $item->getRootTerms();
        $ids = $this->collectIds($rootTerms);

        $duplicateIds = [];

        foreach ($ids as $key => $val) {
            if ($val > 1) {
                $duplicateIds[] = $key;
            }
        }

        return $duplicateIds;
    }


    private function collectIds($terms, array &$ids = []): array
    {
        foreach ($terms as $term) {
            $termId = $term->getAlt(self::ALT_NAME_DKULT_TERM_IDENTIFIER);

            if (is_null($termId)) {
                return $ids;
            }

            if (!isset($ids[$termId])) {
                $ids[$termId] = 0;
            }

            $ids[$termId] += 1;

            $this->collectIds($term->getSubTerms(), $ids);
        }

        return $ids;
    }


    private function outputDuplicateItemIds(array $duplicateItemIds)
    {
        echo "\nDuplicate Thesaurus item ids: " . implode(', ', $duplicateItemIds) . "\n\n";
    }


    private function getItemsWithMissingAltField(Thesaurus $item, string $altFieldName): array
    {
        $rootTerms = $item->getRootTerms();

        return $this->collectItemsWithMissingAltField($rootTerms, $altFieldName);
    }


    public function collectItemsWithMissingAltField(array $items, string $altFieldName, array &$terms = [], string $concatTermName = ''): array
    {
        foreach ($items as $item) {
            $altFieldValue = $item->getAlt($altFieldName);

            $newConcatTermName = $concatTermName . ' > ' . $item->getTerm();

            if (is_null($altFieldValue)) {
                $terms[] = $newConcatTermName;
            }

            $this->collectItemsWithMissingAltField($item->getSubTerms(), $altFieldName, $terms, $newConcatTermName);
        }

        return $terms;
    }


    private function outputItemsWithMissingAltField(array $itemsWithMissingAltField, string $altFieldName)
    {
        echo "Thesaurus items with missing alt field \"" . $altFieldName . "\" :\n  * ";
        echo implode("\n  * ", $itemsWithMissingAltField) . "\n\n";
    }


    /**
     * @return void
     */
    public function cleanUp()
    {
        $this->item = null;
    }


    public function isDone(): bool
    {
        return $this->done;
    }


    /**
     * @return void
     */
    public function done(ProducerInterface $producer)
    {
        $this->done = true;
    }


    /**
     * @return void
     */
    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }
}
