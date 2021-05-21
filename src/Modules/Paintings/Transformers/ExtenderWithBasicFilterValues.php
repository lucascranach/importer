<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Modules\Paintings\Entities\Search\SearchablePainting;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithBasicFilterValues extends Hybrid
{
    private $attributionMatchRules = [
        'attribution.lucas_cranach_the_elder' => [
            'name' => [
                Language::DE => 'Lucas Cranach der Ältere',
                Language::EN => 'Lucas Cranach the Elder',
            ],
            'suffix' => [
                Language::DE => '',
                Language::EN => '',
            ],
            'prefix' => [
                Language::DE => '',
                Language::EN => '',
            ],
        ],

        'attribution.lucas_cranach_the_elder_and_workshop' => [
            'name' => [
                Language::DE => 'Lucas Cranach der Ältere',
                Language::EN => 'Lucas Cranach the Elder',
            ],
            'suffix' => [
                Language::DE => '.*und Werkstatt.*',
                Language::EN => '.*and Workshop.*',
            ],
        ],

        'attribution.workshop_of_lucas_cranach_the_elder' => [
            'name' => [
                Language::DE => 'Werkstatt Lucas Cranach der Ältere',
                Language::EN => 'Workshop Lucas Cranach the Elder',
            ],
        ],

        'attribution.anonymous_master_from_the_cranach_workshop' => [
            'name' => [
                Language::DE => 'Anonymer Meister der Cranach-Werkstatt',
                Language::EN => 'Anonymous Master from the Cranach Workshop',
            ],
        ],

        /* Anonymous masters -> Begin */

        'attribution.named_masters_from_the_cranach_workshop.antonius_heusler' => [
            'name' => [
                Language::DE => 'Antonius Heusler',
                Language::EN => 'Antonius Heusler',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.augustus_cordes' => [
            'name' => [
                Language::DE => 'Augustus Cordes',
                Language::EN => 'Augustus Cordes',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.hans_doering' => [
            'name' => [
                Language::DE => 'Hans Döring',
                Language::EN => 'Hans Döring',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.hans_kemmer' => [
            'name' => [
                Language::DE => 'Hans Kemmer',
                Language::EN => 'Hans Kemmer',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.hans_maler' => [
            'name' => [
                Language::DE => 'Hans Maler',
                Language::EN => 'Hans Maler',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.heinrich_vogtherr' => [
            'name' => [
                Language::DE => 'Heinrich Vogtherr',
                Language::EN => 'Heinrich Vogtherr',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.master_h_b__with_a_griffin_s_head' => [
            'name' => [
                Language::DE => 'Meister H\.B\. mit dem Greifenkopf',
                Language::EN => 'Master H\.B\. with a griffin\'s head',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.master_i_s_' => [
            'name' => [
                Language::DE => 'Meister I\.S\.',
                Language::EN => 'Master I\.S\.',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.master_of_the_mass_of_st_gregory' => [
            'name' => [
                Language::DE => 'Meister der Gregorsmesse',
                Language::EN => 'Master of the Mass of St Gregory',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.master_of_the_doebeln_altarpiece' => [
            'name' => [
                Language::DE => 'Meister des Döbelner Hochaltars',
                Language::EN => 'Master of the Döbeln Altarpiece',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.master_of_the_pflock_altarpiece' => [
            'name' => [
                Language::DE => 'Meister des Pflockschen Altars',
                Language::EN => 'Master of the Pflock Altarpiece',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.master_monogramist_i_w_' => [
            'name' => [
                Language::DE => 'Meister\/Monogrammist I\.W\.',
                Language::EN => 'Master\/Monogramist I\.W\.',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.matthias_gruenewald' => [
            'name' => [
                Language::DE => 'Matthias Grünewald',
                Language::EN => 'Matthias Grünewald',
            ],
            'prefix' => [
                Language::DE => '',
                Language::EN => '',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.peter_rodelstedt' => [
            'name' => [
                Language::DE => 'Peter Rodelstedt',
                Language::EN => 'Peter Rodelstedt',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.pseudo_matthias_gruenewald' => [
            'name' => [
                Language::DE => 'Matthias Grünewald',
                Language::EN => 'Matthias Grünewald',
            ],
            'prefix' => [
                Language::DE => 'Pseudo-',
                Language::EN => 'Pseudo-',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.simon_franck' => [
            'name' => [
                Language::DE => 'Simon Franck',
                Language::EN => 'Simon Franck',
            ],
        ],

        'attribution.named_masters_from_the_cranach_workshop.wolfgang_krodel_the_elder' => [
            'name' => [
                Language::DE => 'Wolfgang Krodel d\. Ä\.',
                Language::EN => 'Wolfgang Krodel the Elder',
            ],
        ],

        /* Anonymous masters -> End */

        'attribution.follower_of_lucas_cranach_the_elder' => [
            'suffix' => [
                Language::DE => '.*nachahmer.*',
                Language::EN => '.*followe.*',
            ],
        ],

        'attribution.circle_of_lucas_cranach_the_elder' => [
            'suffix' => [
                Language::DE => '.*umkreis.+ältere.*',
                Language::EN => '.*circle.+elder.*',
            ],
        ],

        'attribution.copy_after_lucas_cranach_the_elder' => [
            'suffix' => [
                Language::DE => '.*kopie.+ältere.*',
                Language::EN => '.*copy.+elder.*',
            ],
        ],

        'attribution.lucas_cranach_the_younger' => [
            'name' => [
                Language::DE => 'Lucas Cranach der Jüngere',
                Language::EN => 'Lucas Cranach the Younger',
            ],
            'suffix' => [
                Language::DE => '.*und Werkstatt.*',
                Language::EN => '.*and Workshop.*',
            ],
        ],

        'attribution.lucas_cranach_the_younger_and_workshop' => [
            'name' => [
                Language::DE => '.*werk.+jüng.*',
                Language::EN => '.*work.+younger.*',
            ]
        ],

        'attribution.hans_cranach' => [
            'name' => [
                Language::DE => 'Hans Cranach',
                Language::EN => 'Hans Cranach',
            ]
        ]
    ];


    private function __construct()
    {
    }


    public static function new(): self
    {
        $transformer = new self;

        return $transformer;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof SearchablePainting)) {
            throw new Error('Pushed item is not of expected class \'SearchablePainting\'');
        }

        $this->extendWithBasicFilterValues($item);

        $this->next($item);
        return true;
    }


    private function extendWithBasicFilterValues(SearchablePainting $item): void
    {
        $basicFilters = [];

        $this->extendBasicFiltersForAttribution($item, $basicFilters);

        $item->addBasicFilters($basicFilters);
    }


    private function extendBasicFiltersForAttribution(
        SearchablePainting $item,
        array &$basicFilters
    ):void {
        $metadata = $item->getMetadata();
        if (is_null($metadata)) {
            return;
        }

        $langCode = $metadata->getLangCode();

        foreach ($item->getPersons() as $person) {
            foreach ($this->attributionMatchRules as $filterId => $matchRule) {
                $isAMatch = true;

                if (isset($matchRule['name']) && isset($matchRule['name'][$langCode])) {
                    $isAMatch = $this->matchesFieldValue(
                        $matchRule['name'][$langCode],
                        $person->getName(),
                    );
                }

                if (isset($matchRule['suffix']) && $isAMatch && isset($matchRule['suffix'][$langCode])) {
                    $isAMatch = $this->matchesFieldValue(
                        $matchRule['suffix'][$langCode],
                        $person->getSuffix(),
                    );
                }

                if (isset($matchRule['prefix']) && $isAMatch && isset($matchRule['prefix'][$langCode])) {
                    $isAMatch = $this->matchesFieldValue(
                        $matchRule['prefix'][$langCode],
                        $person->getPrefix(),
                    );
                }

                if ($isAMatch) {
                    $basicFilters[$filterId] = true;
                }
            }
        }
    }


    private function matchesFieldValue($ruleValue, $value): bool
    {
        if (empty($ruleValue) && empty($value)) {
            return true;
        }

        return !!preg_match('/' . $ruleValue . '/i', $value);
    }
}
