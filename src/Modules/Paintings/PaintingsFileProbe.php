<?php

namespace CranachDigitalArchive\Importer\Modules\Paintings;

use CranachDigitalArchive\Importer\Interfaces\IFileProbe;

final class PaintingsFileProbe implements IFileProbe
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
        return preg_match("/^cda[_-]daten/i", basename($filepath)) !== 0;
    }
}
