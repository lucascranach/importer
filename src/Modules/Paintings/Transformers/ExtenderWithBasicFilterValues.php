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
