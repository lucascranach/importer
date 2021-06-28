<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\Thesaurus;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\ThesaurusTerm;

/**
 * Reduced Thesaurus export on memory base
 */
class ReducedThesaurusMemoryExporter implements IMemoryExporter
{
    private $item = null;
    private $reducedTermIds = [];
    private $thesaurusTermCounter = 0;
    private $skippedThesaurusTermCounter = 0;


    private function __construct()
    {
    }


    public static function new(ThesaurusMemoryExporter $thesaurusMemoryExporter, array $reducedTermIds = []): self
    {
        $self = new self();

        $self->reducedTermIds = $reducedTermIds;
        $self->item = $self->applyRestrictions($thesaurusMemoryExporter->getData());

        if ($self->thesaurusTermCounter !== 0) {
            echo "Skipped " . $self->skippedThesaurusTermCounter . " of " . $self->thesaurusTermCounter . " thesaurus terms for the reduced thesaurus!\n";
        }

        return $self;
    }


    public function getData(): Thesaurus
    {
        return $this->item;
    }


    public function findByFields(array $fieldValues)
    {
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


    /**
     * @return void
     */
    public function cleanUp()
    {
        $this->item = null;
    }


    private function applyRestrictions(Thesaurus $item)
    {
        if (empty($this->reducedTermIds)) {
            return $item;
        }

        $newItem = new Thesaurus();
        $restrictedRootTerms = $this->reduceTermList($item->getRootTerms(), $this->reducedTermIds);
        foreach ($restrictedRootTerms as $rootTerm) {
            $newItem->addRootTerm($rootTerm);
        }

        return $newItem;
    }


    private function reduceTermList($terms, $metaReferenceIds): array
    {
        $newTerms = [];

        foreach ($terms as $term) {
            $this->thesaurusTermCounter += 1;
            $termId = $this->getTermId($term);

            $newTermSubTerms = $this->reduceTermList($term->getSubTerms(), $metaReferenceIds);

            if (empty($newTermSubTerms) && (is_null($termId) || !in_array($termId, $metaReferenceIds, true))) {
                $this->skippedThesaurusTermCounter += 1;
                continue;
            }

            $newTerm = new ThesaurusTerm();
            $newTerm->setTerm($term->getTerm());

            foreach ($term->getAlts() as $altKey => $altValue) {
                $newTerm->addAlt($altKey, $altValue);
            }

            foreach ($newTermSubTerms as $newTermSubTerm) {
                $newTerm->addSubTerm($newTermSubTerm);
            }

            $newTerms[] = $newTerm;
        }

        return $newTerms;
    }


    private function getTermId(ThesaurusTerm $term)
    {
        $alt = $term->getAlt(ThesaurusTerm::ALT_DKULT_TERM_IDENTIFIER);
        return !is_null($alt) ? $alt : null;
    }
}
