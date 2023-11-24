<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

use CranachDigitalArchive\Importer\Language;

/**
 * Representing a dating including multiple historic event informations
 */
class Dating
{
    private static $isDatedCodes = [
        '[datiert]',
        '[dated]',
    ];

    public $dated = '';
    public $begin = null;
    public $end = null;
    public $remarks = '';
    public $historicEventInformations = [];
    public $isDated = false;


    public function __construct()
    {
    }


    public function setDated(string $dated): void
    {
        $this->dated = $dated;
    }


    public function getDated(): string
    {
        return $this->dated;
    }


    public function setBegin(int $begin): void
    {
        $this->begin = $begin;
    }


    public function getBegin(): ?int
    {
        return $this->begin;
    }


    public function setEnd(int $end): void
    {
        $this->end = $end;
    }


    public function getEnd(): ?int
    {
        return $this->end;
    }


    public function setRemarks(string $remark): void
    {
        $this->remarks = $remark;
    }


    public function getRemarks(): string
    {
        return $this->remarks;
    }


    public function addHistoricEventInformation(HistoricEventInformation $historicEventInformation): void
    {
        if($historicEventInformation->editionNumber>0) {
            $this->historicEventInformations[$historicEventInformation->editionNumber-1]=$historicEventInformation;
        } else {
            $this->historicEventInformations[] = $historicEventInformation;
        }
        /*        if($historicEventInformation->editionNumber>0) {
                    usort($this->historicEventInformations, function ($a, $b) {return strcmp($a->editionNumber, $b->editionNumber);});
                }
        */
    }


    public function getHistoricEventInformations(): array
    {
        return $this->historicEventInformations;
    }


    public function setIsDated(bool $isDated): void
    {
        $this->isDated = $isDated;
    }


    public function getIsDated(): bool
    {
        return $this->isDated;
    }


    public static function determineIfIsDated(string $value): bool
    {
        return in_array($value, self::$isDatedCodes, true);
    }
}
