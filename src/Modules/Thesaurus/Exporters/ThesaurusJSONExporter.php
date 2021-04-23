<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters;

use Error;

use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\Thesaurus;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\ThesaurusTerm;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

/**
 * Graphics restoration exporter on a json flat file base
 */
class ThesaurusJSONExporter extends Consumer implements IFileExporter
{
    private $destFilepath = null;
    private $reducedTermIds = [];
    private $item = null;
    private $done = false;
    private $thesaurusTermCounter = 0;
    private $skippedThesaurusTermCounter = 0;


    private function __construct()
    {
    }


    /**
     * @return self
     */
    public static function withDestinationAt(string $destFilepath, $reducedTermIds = [])
    {
        $exporter = new self();
        $exporter->destFilepath = $destFilepath;
        $exporter->reducedTermIds = $reducedTermIds;
        return $exporter;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof Thesaurus)) {
            throw new Error('Pushed item is not of expected class \'Thesaurus\'');
        }

        if ($this->done) {
            throw new \Error('Can\'t push more items after done() was called!');
        }

        $this->item = $this->applyRestrictions($item);

        return true;
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
        if (is_null($this->destFilepath)) {
            throw new \Error('No filepath for JSON thesaurus export set!');
        }

        $data = json_encode($this->item, JSON_PRETTY_PRINT);
        $dirname = dirname($this->destFilepath);

        if (!file_exists($dirname)) {
            @mkdir($dirname, 0777, true);
        }

        file_put_contents($this->destFilepath, $data);

        $this->done = true;
        $this->item = null;

        if ($this->thesaurusTermCounter !== 0) {
            echo "Skipped " . $this->skippedThesaurusTermCounter . " of " . $this->thesaurusTermCounter . " thesaurus terms for the reduced thesaurus!\n";
        }
    }


    /**
     * @return void
     */
    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
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
        $idFieldName = 'dkultTermIdentifier';
        $alts = $term->getAlts();

        return isset($alts[$idFieldName]) ? $alts[$idFieldName] : null;
    }
}
