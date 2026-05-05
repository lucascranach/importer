<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaReference;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Exporters\ThesaurusMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\ThesaurusTerm;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

/**
 * Extends graphics keywords with parent terms from thesaurus hierarchy
 * If a graphic has keyword "01050501" (Clair-obscure), it will also get "010505" (Holzschnitt)
 */
class KeywordParentTermExtender extends Hybrid
{
    private $thesaurus = null;
    private $keywordType = 'Schlagwort';


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
        if (!($item instanceof GraphicLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'GraphicLanguageCollection\'');
        }

        $this->extendCollectionWithParentTerms($item);

        $this->next($item);
        return true;
    }


    public function done(IProducer $producer)
    {
        parent::done($producer);
        $this->cleanUp();
    }


    private function extendCollectionWithParentTerms(GraphicLanguageCollection $collection): void
    {
        /** @var \CranachDigitalArchive\Importer\Modules\Graphics\Interfaces\IGraphic $graphic */
        foreach ($collection as $graphic) {
            $existingKeywords = $graphic->getKeywords();
            $newKeywords = [];

            foreach ($existingKeywords as $keyword) {
                if ($keyword->getType() !== $this->keywordType) {
                    continue;
                }

                // Find the term hierarchy in thesaurus
                $termHierarchy = $this->findTermHierarchyInThesaurus($keyword->getTerm());

                // Add parent terms as new keywords (skip the last one as it's already in the list)
                for ($i = 0; $i < count($termHierarchy) - 1; $i++) {
                    $parentTerm = $termHierarchy[$i];
                    $parentTermId = $this->getIdForTerm($parentTerm);

                    if (!is_null($parentTermId)) {
                        // Check if this parent term is already in keywords
                        $alreadyExists = false;
                        foreach ($existingKeywords as $existingKeyword) {
                            if ($existingKeyword->getTerm() === $parentTermId) {
                                $alreadyExists = true;
                                break;
                            }
                        }

                        if (!$alreadyExists) {
                            $parentKeyword = new MetaReference();
                            $parentKeyword->setType($this->keywordType);
                            $parentKeyword->setTerm($parentTermId);
                            $parentKeyword->setPath($keyword->getPath()); // Use same path as child
                            $newKeywords[] = $parentKeyword;
                        }
                    }
                }
            }

            // Add all new parent keywords to the graphic
            foreach ($newKeywords as $newKeyword) {
                $collection->addKeyword($newKeyword);
            }
        }
    }


    private function findTermHierarchyInThesaurus(string $identifier): array
    {
        $foundHierarchy = [];

        foreach ($this->thesaurus->getRootTerms() as $rootTerm) {
            $result = $this->getFlattenedHierarchyOfTerm($identifier, $rootTerm);

            if (count($result) > 0) {
                $foundHierarchy = $result;
                break;
            }
        }

        return $foundHierarchy;
    }


    private function getFlattenedHierarchyOfTerm(string $identifier, ThesaurusTerm $term): array
    {
        $termList = [];
        $dKultIdentifier = $this->getIdForTerm($term);

        if (!is_null($dKultIdentifier) && $dKultIdentifier === $identifier) {
            // Found the term - return it
            $termList = [$term];
        } else {
            // Check sub-terms
            foreach ($term->getSubTerms() as $subTerm) {
                $result = $this->getFlattenedHierarchyOfTerm($identifier, $subTerm);

                if (count($result) > 0) {
                    // Found in sub-term - prepend current term to create hierarchy
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


    private function cleanUp(): void
    {
        $this->thesaurus = null;
    }
}
