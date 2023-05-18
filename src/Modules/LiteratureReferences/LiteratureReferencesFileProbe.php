<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences;

use CranachDigitalArchive\Importer\Interfaces\IFileProbe;

final class LiteratureReferencesFileProbe implements IFileProbe
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
        return preg_match("/^cda[_-]literatur/i", basename($filepath)) !== 0;
    }
}
