<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use Error;

class EditionDeterminer extends Hybrid
{
    private static $editionGermanMappings = [
        [
            'patterns' => [
                '/\b(1\. Auflage)\b/',
            ],
            'value' =>  1,
        ],
        [
            'patterns' => [
                '/\b(2\. Auflage)\b/',
            ],
            'value' => 2,
        ],
        [
            'patterns' => [
                '/\b(3\. Auflage)\b/',
            ],
            'value' => 3,
        ],
        [
            'patterns' => [
                '/\b(4\. Auflage)\b/',
            ],
            'value' => 4,
        ],
        [
            'patterns' => [
                '/\b(5\. Auflage)\b/',
            ],
            'value' => 5,
        ],
        [
            'patterns' => [
                '/\b(6\. Auflage)\b/',
            ],
            'value' => 6,
        ],
        [
            'patterns' => [
                '/\b(7\. Auflage)\b/',
            ],
            'value' => 7,
        ],
        [
            'patterns' => [
                '/\b(8\. Auflage)\b/',
            ],
            'value' => 8,
        ],
        [
            'patterns' => [
                '/\b(9\. Auflage)\b/',
            ],
            'value' => 9,
        ],
        [
            'patterns' => [
                '/\b(10\. Auflage)\b/',
            ],
            'value' => 10,
        ],
        [
            'patterns' => [
                '/\b(11\. Auflage)\b/',
            ],
            'value' => 11,
        ],
        [
            'patterns' => [
                '/\b(12\. Auflage)\b/',
            ],
            'value' => 12,
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

        $item->setEditionNumber($this->getEditionNumber($item));

        $this->next($item);
        return true;
    }


    private function getEditionNumber(GraphicLanguageCollection $graphicCollection, int $editionNumber = 0): int
    {
        /* We derive the condition level from the german condition text */
        $germanGraphic = $graphicCollection->get(Language::DE);

        $classification = $germanGraphic->getClassification();

        if (is_null($classification)) {
            return $editionNumber;
        }

        $condition = trim($classification->getCondition());

        /* We check the condition against the mappings */

        foreach (self::$editionGermanMappings as $editionMapping) {

            foreach ($editionMapping['patterns'] as $pattern) {

                if (preg_match($pattern, $condition) === 1) {
                    return $editionMapping['value'];
                }
            }
        }
        return $editionNumber;
    }


    /**
     * @return void
     */
    public function done(IProducer $producer)
    {
        parent::done($producer);
    }
}
