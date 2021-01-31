<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus\Inflators\XML;

use Error;
use SimpleXMLElement;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\Thesaurus;
use CranachDigitalArchive\Importer\Modules\Thesaurus\Entities\ThesaurusTerm;

/**
 * Thesaurus inflator
 * 	by traversing the xml element node and extracting the data in a structured way
 */
class ThesaurusInflator implements IInflator
{
    private static $rootTermElement = 'root-term';
    private static $termElement = 'term';
    private static $altTermElement = 'alt-term';

    private static $altTermAttributeKeyMapping = [
        'British Equivalent' => 'britishEquivalent',
        'dkult Term Identifier' => 'dkultTermIdentifier',
        'TermID des AAT' => 'aatTermId',
    ];

    private function __construct()
    {
    }


    public static function inflate(
        SimpleXMLElement $node,
        Thesaurus $thesaurus
    ): void {
        $subNode = $node->{self::$rootTermElement};

        self::inflateTerms($subNode, $thesaurus);
    }


    /* Term */
    /**
     * @return array
     *
     * @psalm-return array<empty, empty>
     */
    private static function inflateTerms(
        SimpleXMLElement $node,
        Thesaurus $thesaurus
    ): array {
        $mappedTerms = [];

        foreach ($node->children() as $termElement) {
            if (!is_null($termElement) && $termElement->getName() === self::$termElement) {
                $thesaurus->addRootTerm(self::mapTerm($termElement));
            }
        }

        return $mappedTerms;
    }


    private static function mapTerm(SimpleXMLElement $termElement): ThesaurusTerm
    {
        $thesaurusTerm = new ThesaurusTerm;

        $attributes = $termElement->attributes();

        if (!is_null($attributes)) {
            foreach ($attributes as $attribute) {
                if (!is_null($attribute) && $attribute->getName() === 'term') {
                    $thesaurusTerm->setTerm(strval($attribute));
                }
            }
        }

        foreach ($termElement->children() as $subElement) {
            if (is_null($subElement)) {
                continue;
            }

            if ($subElement->getName() === self::$altTermElement) {
                self::inflateTermAlt($subElement, $thesaurusTerm);
            } else {
                $thesaurusTerm->addSubTerm(self::mapTerm($subElement));
            }
        }

        return $thesaurusTerm;
    }

    private static function inflateTermAlt(
        SimpleXMLElement $altTermElement,
        ThesaurusTerm $thesaurusTerm
    ): void {
        $type = '';
        $term = '';

        $attributes = $altTermElement->attributes();

        if (!is_null($attributes)) {
            foreach ($attributes as $name => $value) {
                switch ($name) {
                    case 'type':
                        $type = strval($value);
                        break;
                    case 'term':
                        $term = strval($value);
                        break;
                    default:
                }
            }
        }

        if (isset(self::$altTermAttributeKeyMapping[$type])) {
            $thesaurusTerm->addAlt(self::$altTermAttributeKeyMapping[$type], $term);
        }
    }
}
