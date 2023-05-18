<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations;

use CranachDigitalArchive\Importer\Interfaces\IFileProbe;

final class GraphicsRestorationsFileProbe implements IFileProbe
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
        return preg_match("/^cda[_-]gr[_-]restdokumente/i", basename($filepath)) !== 0;
    }
}
