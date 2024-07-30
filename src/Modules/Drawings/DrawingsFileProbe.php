<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings;

use CranachDigitalArchive\Importer\Interfaces\IFileProbe;

final class DrawingsFileProbe implements IFileProbe
{
    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    public function probe(string $filepath): bool
    {
        return preg_match("/^cda([_-]export)?[_-]dr[_-]daten/i", basename($filepath)) !== 0;
    }
}
