<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics;

use CranachDigitalArchive\Importer\Interfaces\IFileProbe;

final class GraphicsFileProbe implements IFileProbe
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
        return preg_match("/^cda[_-]gr[_-]daten/i", basename($filepath)) !== 0;
    }
}
