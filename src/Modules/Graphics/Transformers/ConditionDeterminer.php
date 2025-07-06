<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use Error;

class ConditionDeterminer extends Hybrid
{
    private static $conditionGermanMappings = [
        [
            'patterns' => [
                '/^I\.?\s*(zustand|auflage)/i',
            ],
            'value' =>  1,
        ],
        [
            'patterns' => [
                '/^II\.?\s*(zustand|auflage)/i',
            ],
            'value' => 2,
        ],
        [
            'patterns' => [
                '/^III\.?\s*(zustand|auflage)/i',
            ],
            'value' => 3,
        ],
        [
            'patterns' => [
                '/^IV\.?\s*(zustand|auflage)/i',
            ],
            'value' => 4,
        ],
        [
            'patterns' => [
                '/^V\.?\s*(zustand|auflage)/i',
            ],
            'value' => 5,
        ],
        [
            'patterns' => [
                '/^VI\.?\s*(zustand|auflage)/i',
            ],
            'value' => 6,
        ],
        [
            'patterns' => [
                '/^VII\.?\s*(zustand|auflage)/i',
            ],
            'value' => 7,
        ],
        [
            'patterns' => [
                '/^VIII\.?\s*(zustand|auflage)/i',
            ],
            'value' => 8,
        ],
        [
            'patterns' => [
                '/^IX\.?\s*(zustand|auflage)/i',
            ],
            'value' => 9,
        ],
        [
            'patterns' => [
                '/^X\.?\s*(zustand|auflage)/i',
            ],
            'value' => 10,
        ],
        [
            'patterns' => [
                '/^Einziger\.?\s*(zustand|auflage)/i',
            ],
            'value' => 100,
        ],
    ];


    private function __construct()
    {
    }


    public static function new(): self
    {
        return new self();
    }

    public function handleItem($item): bool
    {
        if (!($item instanceof GraphicLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'GraphicLanguageCollection\'');
        }

        $item->setConditionLevel($this->getConditionLevel($item));

        $this->next($item);
        return true;
    }


    private function getConditionLevel(GraphicLanguageCollection $graphicCollection, int $conditionLevel = 0): int
    {
        /* We derive the condition level from the german condition text */
        $germanGraphic = $graphicCollection->get(Language::DE);

        $classification = $germanGraphic->getClassification();
        $inventoryNumber = $germanGraphic->getInventoryNumber();

        if (is_null($classification)) {
            return $conditionLevel;
        }

        $condition = trim($classification->getCondition());

        foreach (self::$conditionGermanMappings as $conditionMapping) {

            foreach ($conditionMapping['patterns'] as $pattern) {
                if (preg_match($pattern, $condition) === 1) {
                    return $conditionMapping['value'];
                }
            }
        }

        return $conditionLevel;
    }


    /**
     * @return void
     */
    public function done(IProducer $producer)
    {
        parent::done($producer);
    }
}
