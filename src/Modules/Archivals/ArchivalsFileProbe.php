<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals;

use CranachDigitalArchive\Importer\Interfaces\IFileProbe;

final class ArchivalsFileProbe implements IFileProbe
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
        return preg_match("/^cda[_-]a[_-]daten/i", basename($filepath)) !== 0;
    }
}
