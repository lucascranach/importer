<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use Error;

class ConditionDeterminer extends Hybrid
{
    private static $conditionLangMappings = [
        Language::DE => [
            [
                'patterns' => [
                    '/^I\.\s*zustand/i',
                    '/^1\.\s*auflage/i',
                ],
                'value' =>  1,
            ],
            [
                'patterns' => [
                    '/^II\.\s*zustand/i',
                    '/^2\.\s*auflage/i',
                ],
                'value' => 2,
            ],
            [
                'patterns' => [
                    '/^III\.\s*zustand/i',
                    '/^3\.\s*auflage/i',
                ],
                'value' => 3,
            ],
            [
                'patterns' => [
                    '/^IV\.\s*zustand/i',
                    '/^4\.\s*auflage/i',
                ],
                'value' => 4,
            ],
            [
                'patterns' => [
                    '/^V\.\s*zustand/i',
                    '/^5\.\s*auflage/i',
                ],
                'value' => 5,
            ],
            [
                'patterns' => [
                    '/^VI\.\s*zustand/i',
                    '/^6\.\s*auflage/i',
                ],
                'value' => 6,
            ],
            [
                'patterns' => [
                    '/^VII\.\s*zustand/i',
                    '/^7\.\s*auflage/i',
                ],
                'value' => 7,
            ],
            [
                'patterns' => [
                    '/^VIII\.\s*zustand/i',
                    '/^8\.\s*auflage/i',
                ],
                'value' => 8,
            ],
            [
                'patterns' => [
                    '/^IX\.\s*zustand/i',
                    '/^9\.\s*auflage/i',
                ],
                'value' => 9,
            ],
            [
                'patterns' => [
                    '/^X\.\s*zustand/i',
                    '/^10\.\s*auflage/i',
                ],
                'value' => 10,
            ],
        ],
        Language::EN => [
            [
                'patterns' => [
                    '/^1st\s*state/i',
                    '/^1st\s*edition/i',
                ],
                'value' =>  1,
            ],
            [
                'patterns' => [
                    '/^2nd\s*state/i',
                    '/^2nd\s*edition/i',
                ],
                'value' => 2,
            ],
            [
                'patterns' => [
                    '/^3rd\s*state/i',
                    '/^3rd\s*edition/i',
                ],
                'value' => 3,
            ],
            [
                'patterns' => [
                    '/^4th\s*state/i',
                    '/^4th\s*edition/i',
                ],
                'value' => 4,
            ],
            [
                'patterns' => [
                    '/^5th\s*state/i',
                    '/^5th\s*edition/i',
                ],
                'value' => 5,
            ],
            [
                'patterns' => [
                    '/^6th\s*state/i',
                    '/^6th\s*edition/i',
                ],
                'value' => 6,
            ],
            [
                'patterns' => [
                    '/^7th\s*state/i',
                    '/^7th\s*edition/i',
                ],
                'value' => 7,
            ],
            [
                'patterns' => [
                    '/^8th\s*state/i',
                    '/^8th\s*edition/i',
                ],
                'value' => 8,
            ],
            [
                'patterns' => [
                    '/^9th\s*state/i',
                    '/^9th\s*edition/i',
                ],
                'value' => 9,
            ],
            [
                'patterns' => [
                    '/^10th\s*state/i',
                    '/^10th\s*edition/i',
                ],
                'value' => 10,
            ],
        ],
    ];
    private $conditionLevelCache = [];


    private function __construct()
    {
    }


    public static function new(): self
    {
        return new self;
    }

    public function handleItem($item): bool
    {
        if (!($item instanceof Graphic)) {
            throw new Error('Pushed item is not of expected class \'Graphic\'');
        }

        $inventoryNumber = $item->getInventoryNumber();

        if (!isset($this->conditionLevelCache[$inventoryNumber])) {
            $this->conditionLevelCache[$inventoryNumber] = $this->getConditionLevel(
                $item,
                $item->getConditionLevel(),
            );
        }

        $item->setConditionLevel($this->conditionLevelCache[$inventoryNumber]);

        $this->next($item);
        return true;
    }


    private function getConditionLevel(Graphic $graphic, int $conditionLevel = 0): int
    {
        $classification = $graphic->getClassification();

        $conditionLangMappingExists = isset(self::$conditionLangMappings[$graphic->getLangCode()]);

        if (is_null($classification) || !$conditionLangMappingExists) {
            return $conditionLevel;
        }

        $condition = trim($classification->getCondition());
        $conditionMappings = self::$conditionLangMappings[$graphic->getLangCode()];

        foreach ($conditionMappings as $conditionMapping) {
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
    public function done(ProducerInterface $producer)
    {
        parent::done($producer);
        $this->cleanUp();
    }


    private function cleanUp(): void
    {
        $this->conditionLevelCache = [];
    }
}
