<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Search\FilterInfoItem;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ThesaurusMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\ThesaurusTerm;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search\SearchablePaintingLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithThesaurus extends Hybrid
{
    private $thesaurus = null;
    private $langToAltKey = [
        Language::EN => 'britishEquivalent',
    ];
    private $keywordType = 'Schlagwort';
    private $numericIdToGeneralIdMap = [
        '0101' => 'function',
        '0102' => 'form',
        '0103' => 'component_parts',
        '0104' => 'subject',
        '0105' => 'technique',
    ];


    private function __construct()
    {
    }


    public static function new(ThesaurusMemoryExporter $thesaurusMemoryExporter): self
    {
        $transformer = new self;

        $transformer->thesaurus = $thesaurusMemoryExporter->getData();

        return $transformer;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof SearchablePaintingLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'SearchablePaintingLanguageCollection\'');
        }

        $this->extendCollectionWithThesaurusFilterInfos($item);

        $this->next($item);
        return true;
    }


    /**
     * @return void
     */
    public function done(ProducerInterface $producer)
    {
        parent::done($producer);
        $this->cleanUp();
    }


    private function extendCollectionWithThesaurusFilterInfos(SearchablePaintingLanguageCollection $collection): void
    {
        foreach ($collection as $painting) {
            foreach ($painting->getKeywords() as $keyword) {
                if ($keyword->getType() !== $this->keywordType) {
                    continue;
                }

                $res = $this->findKeywordIdentifierInThesaurus($keyword->getTerm());

                $metadata = $painting->getMetadata();
                $langCode = !is_null($metadata) ? $metadata->getLangCode() : '';

                $mappedItems = $this->mapThesaurusTermChainToFilterInfoChain($res, $langCode);
                $firstItem = array_shift($mappedItems);

                if (!is_null($firstItem)) {
                    $painting->addFilterInfoCategoryItems($firstItem->getId(), $mappedItems);
                }
            }
        }
    }


    private function findKeywordIdentifierInThesaurus(string $identifier): array
    {
        $foundFlattenedTermHierarchies = [];

        foreach ($this->thesaurus->getRootTerms() as $rootTerm) {
            $result = $this->getFlattenedHierarchyOfTerm($identifier, $rootTerm);

            if (count($result) > 0) {
                $foundFlattenedTermHierarchies = $result;
            }
        }

        return $foundFlattenedTermHierarchies;
    }


    private function getFlattenedHierarchyOfTerm(string $identifier, ThesaurusTerm $term): array
    {
        $termList = [];

        $dKultIdentifier = $this->getIdForTerm($term);

        if (!is_null($dKultIdentifier) && $dKultIdentifier === $identifier) {
            $termList = [$term];
        } else {
            foreach ($term->getSubTerms() as $subTerm) {
                $result = $this->getFlattenedHierarchyOfTerm($identifier, $subTerm);

                if (count($result) > 0) {
                    $termList = array_merge([$term], $result);
                    break;
                }
            }
        }

        return $termList;
    }


    private function getIdForTerm(ThesaurusTerm $term): ?string
    {
        return $term->getAlt(ThesaurusTerm::ALT_DKULT_TERM_IDENTIFIER);
    }


    private function mapThesaurusTermChainToFilterInfoChain(array $terms, string $langCode): array
    {
        $items = [];

        $prevId = '';

        for ($i = 0; $i < count($terms); $i += 1) {
            $currTerm = $terms[$i];

            $id = $this->getIdForTerm($currTerm);

            $item = new FilterInfoItem();

            if (!is_null($id)) {
                $item->setId($id);
            }

            $term = $currTerm->getTerm();

            if (isset($this->langToAltKey[$langCode])) {
                $altKey = $this->langToAltKey[$langCode];
                $altValue = $currTerm->getAlt($altKey);

                if (!is_null($altValue)) {
                    $term = $altValue;
                }
            }

            $item->setText($term);

            if ($i > 0 && !is_null($prevId)) {
                $item->setParentId($prevId);
            }

            $prevId = $id;

            $items[] = $item;
        }

        return $items;
    }


    private function cleanUp(): void
    {
        $this->thesaurus = null;
    }
}
