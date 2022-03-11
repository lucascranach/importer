<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

use CranachDigitalArchive\Importer\Language;

/**
 * Representing a single object reference by inventory number
 */
class ObjectReference
{
    private static $inventoryNumberPrefixPatterns = [
        '/^GWN_/' => 'GWN_',
        '/^CDA\./' => 'CDA.',
        '/^CDA_/' => 'CDA_',
        '/^G_G_/' => 'G_G_',
        '/^G_/' => 'G_',
    ];

    private static $kindPatternMappings = [
        '/(inhaltlich\s+verwandt\s+mit|version)/i' => 'RELATED_IN_CONTENT_TO',
        '/gehört\s+thematisch\s+zu/i' => 'SIMILAR_TO',
        '/gehört\s+zu/i' => 'BELONGS_TO',
        '/aufgelegt\s+mit/i' => 'GRAPHIC', // ???
        '/teil\s+eines\s+werkes/i' => 'PART_OF_WORK',
        '/gegenstück\s*\/\s*pendant/i' => 'COUNTERPART_TO',
        '/abzug/i' => 'REPRINT_OF',
    ];

    private static $remarksMappings = [
        Language::DE => [
            '01' => 'ähnlicher Entstehungszeitraum',
            '02' => 'ganzfigurige Komposition',
            '03' => 'halbfigurige Komposition',
            '06' => 'in ähnlicher Haltung',
            '07' => 'vor Landschaftshintergrund',
            '08' => 'vor dunklem Hintergrund',
            '09' => 'vor Hintergrund mit Landschaftsausschnitt',
            '16' => 'ähnliche Komposition',
            '17' => 'Typus ab 1532 ohne Kopfbedeckung',
            '18' => 'Typus ab 1540 ohne Kopfbedeckung',
            '19' => 'Typus ab 1543 mit Kopfbedeckung',
            '20' => 'Typus ab 1559 mit grauem Bart und Schaube',
            '21' => 'Junker Jörg',
            '22' => 'Augustinermönch',
            '23' => 'Typus ab 1525 ohne Kopfbedeckung',
            '24' => 'Typus ab 1528 mit Kopfbedeckung, Blick auf den Betrachter',
            '25' => 'Typus ab 1532 mit Kopfbedeckung, Blick nach rechts',
            '26' => 'Typus ab 1539 ohne Kopfbedeckung, grauhaarig',
            '27' => 'Typus ab 1540 mit Kopfbedeckung, grauhaarig',
            '28' => 'auf dem Sterbebett',
            '78' => 'in zeitgenössischer Kleidung',
            '79' => 'ganzfiguriger Akt',
            '80' => 'Akt, mit der linken Hand die Schaube haltend',
            '81' => 'Akt, mit der linken Hand die Scham bedeckend',
            '82' => 'Akt mit Schaube, den linken Arm zum Kopf erhoben',
            '83' => 'Dolch in der gesenkten rechten Hand vor dem Körper',
            '84' => 'Dolch in beiden Händen',
            '93' => 'Akt',
            '94' => 'Dolch in der linken Hand',
            '95' => 'Dolch in der erhobenen rechten Hand neben dem Körper',
            'graphic' => 'Grafik',
            'header' => 'Verwandte Arbeiten',
            'similar' => 'Vergleichbare Motive',
            'subheader' => 'Weitere Verknüpfungen',
            'support' => 'Bildträger aus dem selben Baum gefertigt',
            'versions' => 'Versionen',
        ],
        Language::EN => [
            '01' => 'created during the same period',
            '02' => 'full-length composition',
            '03' => 'half-length composition',
            '06' => 'similar pose',
            '07' => 'with landscape background',
            '08' => 'with dark background',
            '09' => 'background with a section of a landscape',
            '16' => 'similar composition',
            '17' => 'type by 1532 bareheaded, looking left',
            '18' => 'type by 1540 bareheaded, looking left',
            '19' => 'type by 1543 with hat, looking left',
            '20' => 'type by 1559 with grey beard and coat',
            '21' => 'Junker Jörg',
            '22' => 'Augustine Monk',
            '23' => 'type by 1525/26 bareheaded',
            '24' => 'type by 1528 with hat, looking at beholder',
            '25' => 'type by 1532 with hat, looking right',
            '26' => 'type by 1539 bareheaded, grey-haired',
            '27' => 'type by 1540 with hat, grey-haired',
            '28' => 'on deathbed',
            '78' => 'in contemporary dress',
            '79' => 'full-length nude',
            '80' => 'nude, holding the coat with her left hand',
            '81' => 'nude, with the left hand hiding her vulva',
            '82' => 'nude with coat, left arm raised',
            '83' => 'dagger held in the lowered right hand in front of her body',
            '84' => 'dagger held in both hands',
            '93' => 'nude',
            '94' => 'dagger held in left hand',
            '95' => 'dagger held in the upraised right hand next to her body',
            'graphic' => 'Graphic Art',
            'header' => 'Related Works',
            'similar' => 'Similar Motifs',
            'subheader' => 'Additional Linkage',
            'support' => 'Support made from the same tree',
            'versions' => 'Versions',
        ],
    ];

    public $text = '';
    public $kind = 'UNKNOWN';
    public $inventoryNumberPrefix = '';
    public $inventoryNumber = '';
    public $remarks = [];


    public function __construct()
    {
    }


    public function setText(string $text): void
    {
        $this->text = $text;
    }


    public function getText(): string
    {
        return $this->text;
    }


    public function setKind(string $kind): void
    {
        $this->kind = $kind;
    }


    public function getKind(): string
    {
        return $this->kind;
    }


    public function setInventoryNumberPrefix(string $inventoryNumberPrefix): void
    {
        $this->inventoryNumberPrefix = $inventoryNumberPrefix;
    }


    public function getInventoryNumberPrefix(): string
    {
        return $this->inventoryNumberPrefix;
    }


    public function setInventoryNumber(string $inventoryNumber): void
    {
        $this->inventoryNumber = $inventoryNumber;

        foreach (self::$inventoryNumberPrefixPatterns as $pattern => $value) {
            $count = 0;

            $this->inventoryNumber = preg_replace($pattern, '', $this->inventoryNumber, -1, $count);

            if ($count > 0) {
                $this->setInventoryNumberPrefix($value);
                break;
            }
        }
    }


    public function getInventoryNumber(): string
    {
        return $this->inventoryNumber;
    }


    public function addRemark(string $remark): void
    {
        $this->remarks[] = $remark;
    }


    public function getRemarks(): array
    {
        return $this->remarks;
    }


    public static function getKindFromText(string $text)
    {
        $foundKind = null;

        foreach (self::$kindPatternMappings as $pattern => $kind) {
            if (preg_match($pattern, $text) === 1) {
                $foundKind = $kind;
                break;
            }
        }

        return $foundKind ?? false;
    }


    public static function getRemarkMappingForLangAndCode(string $lang, string $code)
    {
        if (!isset(self::$remarksMappings[$lang]) || !isset(self::$remarksMappings[$lang][$code])) {
            return false;
        }

        return self::$remarksMappings[$lang][$code];
    }
}
