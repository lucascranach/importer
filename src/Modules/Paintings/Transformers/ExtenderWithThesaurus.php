<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Search\ThesaurusItem;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Painting;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search\SearchablePainting;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\Thesaurus;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\ThesaurusTerm;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithThesaurus extends Hybrid
{
    private $thesaurus = null;
    private $langToAltKey = [
        Language::EN => 'britishEquivalent',
    ];


    private function __construct()
    {
    }


    public static function new(Thesaurus $thesaurus)
    {
        $transformer = new self;

        $transformer->thesaurus = $thesaurus;

        return $transformer;
    }

    public function handleItem($item): bool
    {
        if (!($item instanceof Painting)) {
            throw new Error('Pushed item is not of expected class \'Painting\'');
        }

        $newItem = $this->mapToSearchablePainting($item);

        $this->extendWithThesaurusData($newItem);

        $this->next($newItem);
        return true;
    }


    public function done(ProducerInterface $producer)
    {
        parent::done($producer);
        $this->cleanUp();
    }


    private function mapToSearchablePainting(Painting $painting): SearchablePainting
    {
        $searchablePainting = new SearchablePainting();

        foreach ($painting as $key => $value) {
            $searchablePainting->$key = $value;
        }

        return $searchablePainting;
    }


    private function extendWithThesaurusData(SearchablePainting $painting)
    {
        foreach ($painting->getKeywords() as $keyword) {
            if ($keyword->getType() !== 'Schlagwort') {
                continue;
            }

            $res = $this->findKeywordIdentifierInThesaurus($keyword->getTerm());

            $mappedItems = $this->mapThesaurusTermChainToThesaurusItemChain($res, $painting->getLangCode());
            $painting->addThesaurusItems($mappedItems);
        }
    }


    private function findKeywordIdentifierInThesaurus(string $identifier)
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

        $dKultIdentifier = $this->getDKultIdentifierForTerm($term);

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


    private function getDKultIdentifierForTerm(ThesaurusTerm $term): ?string
    {
        $idKey = 'dkultTermIdentifier';
        return $term->getAlt($idKey);
    }


    private function mapThesaurusTermChainToThesaurusItemChain(array $terms, $langCode): array
    {
        $items = [];

        $prevId = '';

        for ($i = 0; $i < count($terms); $i += 1) {
            $currTerm = $terms[$i];

            $id = $this->getDKultIdentifierForTerm($currTerm);

            $item = new ThesaurusItem();
            $item->setId($id);

            $term = $currTerm->getTerm();

            if (isset($this->langToAltKey[$langCode])) {
                $altKey = $this->langToAltKey[$langCode];
                $altValue = $currTerm->getAlt($altKey);

                if (!is_null($altValue)) {
                    $term = $altValue;
                }
            }

            $item->setTerm($term);

            if ($i > 0) {
                $item->setParentId($prevId);
            }

            $prevId = $id;

            $items[] = $item;
        }

        return $items;
    }


    private function cleanUp()
    {
        $this->thesaurus = null;
    }
}
