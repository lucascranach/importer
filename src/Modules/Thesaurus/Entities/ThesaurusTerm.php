<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus\Entities;

/**
 * Representing a single thesaurus term
 */
class ThesaurusTerm
{
    const ALT_BRITISH_EQUIVALENT = 'britishEquivalent';
    const ALT_ALTERNATIVE_TERM = 'alternateTerm';
    const ALT_DKULT_TERM_IDENTIFIER = 'dkultTermIdentifier';
    const ALT_AAT_TERM_ID = 'aatTermId';

    public $term = '';
    public $alt = [];
    public $subTerms = [];


    public function __construct()
    {
    }


    public function setTerm(string $term): void
    {
        $this->term = $term;
    }


    public function getTerm()
    {
        return $this->term;
    }


    public function addAlt(string $key, string $value): void
    {
        $this->alt[$key] = $value;
    }


    public function getAlts(): array
    {
        return $this->alt;
    }


    public function getAlt(string $key): ?string
    {
        $alts = $this->getAlts();
        return isset($alts[$key]) ? $alts[$key] : null;
    }


    public function addSubTerm(ThesaurusTerm $term): void
    {
        $this->subTerms[] = $term;
    }


    public function getSubTerms(): array
    {
        return $this->subTerms;
    }
}
