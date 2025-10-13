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
        ['patterns' => ['/\bAuflage a\?\)/',], 'value' =>  100,],
        ['patterns' => ['/\bAuflage b\?\)/',], 'value' =>  101,],
        ['patterns' => ['/\bAuflage c\?\)/',], 'value' =>  102,],
        ['patterns' => ['/\bAuflage d\?\)/',], 'value' =>  103,],
        ['patterns' => ['/\bAuflage e\?\)/',], 'value' =>  104,],
        ['patterns' => ['/\bAuflage f\?\)/',], 'value' =>  105,],
        ['patterns' => ['/\bAuflage g\?\)/',], 'value' =>  106,],
        ['patterns' => ['/\bAuflage h\?\)/',], 'value' =>  107,],
        ['patterns' => ['/\bAuflage i\?\)/',], 'value' =>  108,],
        ['patterns' => ['/\bAuflage j\?\)/',], 'value' =>  109,],
        ['patterns' => ['/\bAuflage k\?\)/',], 'value' =>  110,],
        ['patterns' => ['/\bAuflage l\?\)/',], 'value' =>  111,],
        ['patterns' => ['/\bAuflage m\?\)/',], 'value' =>  112,],
        ['patterns' => ['/\bAuflage n\?\)/',], 'value' =>  113,],
        ['patterns' => ['/\bAuflage o\?\)/',], 'value' =>  114,],
        ['patterns' => ['/\bAuflage p\?\)/',], 'value' =>  115,],
        ['patterns' => ['/\bAuflage q\?\)/',], 'value' =>  116,],
        ['patterns' => ['/\bAuflage r\?\)/',], 'value' =>  117,],
        ['patterns' => ['/\bAuflage s\?\)/',], 'value' =>  118,],
        ['patterns' => ['/\bAuflage t\?\)/',], 'value' =>  119,],
        ['patterns' => ['/\bAuflage u\?\)/',], 'value' =>  120,],
        ['patterns' => ['/\bAuflage v\?\)/',], 'value' =>  121,],
        ['patterns' => ['/\bAuflage w\?\)/',], 'value' =>  122,],
        ['patterns' => ['/\bAuflage x\?\)/',], 'value' =>  123,],
        ['patterns' => ['/\bAuflage y\?\)/',], 'value' =>  124,],
        ['patterns' => ['/\bAuflage z\?\)/',], 'value' =>  125,],

        ['patterns' => ['/\bAuflage a\b/',], 'value' =>  0,],
        ['patterns' => ['/\bAuflage b\b/',], 'value' =>  1,],
        ['patterns' => ['/\bAuflage c\b/',], 'value' =>  2,],
        ['patterns' => ['/\bAuflage d\b/',], 'value' =>  3,],
        ['patterns' => ['/\bAuflage e\b/',], 'value' =>  4,],
        ['patterns' => ['/\bAuflage f\b/',], 'value' =>  5,],
        ['patterns' => ['/\bAuflage g\b/',], 'value' =>  6,],
        ['patterns' => ['/\bAuflage h\b/',], 'value' =>  7,],
        ['patterns' => ['/\bAuflage i\b/',], 'value' =>  8,],
        ['patterns' => ['/\bAuflage j\b/',], 'value' =>  9,],
        ['patterns' => ['/\bAuflage k\b/',], 'value' =>  10,],
        ['patterns' => ['/\bAuflage l\b/',], 'value' =>  11,],
        ['patterns' => ['/\bAuflage m\b/',], 'value' =>  12,],
        ['patterns' => ['/\bAuflage n\b/',], 'value' =>  13,],
        ['patterns' => ['/\bAuflage o\b/',], 'value' =>  14,],
        ['patterns' => ['/\bAuflage p\b/',], 'value' =>  15,],
        ['patterns' => ['/\bAuflage q\b/',], 'value' =>  16,],
        ['patterns' => ['/\bAuflage r\b/',], 'value' =>  17,],
        ['patterns' => ['/\bAuflage s\b/',], 'value' =>  18,],
        ['patterns' => ['/\bAuflage t\b/',], 'value' =>  19,],
        ['patterns' => ['/\bAuflage u\b/',], 'value' =>  20,],
        ['patterns' => ['/\bAuflage v\b/',], 'value' =>  21,],
        ['patterns' => ['/\bAuflage w\b/',], 'value' =>  22,],
        ['patterns' => ['/\bAuflage x\b/',], 'value' =>  23,],
        ['patterns' => ['/\bAuflage y\b/',], 'value' =>  24,],
        ['patterns' => ['/\bAuflage z\b/',], 'value' =>  25,],
        ['patterns' => ['/\bAuflage unklar\b/',], 'value' =>  26,],
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
