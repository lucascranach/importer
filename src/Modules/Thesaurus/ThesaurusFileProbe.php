<?php

namespace CranachDigitalArchive\Importer\Modules\Thesaurus;

use CranachDigitalArchive\Importer\Interfaces\IFileProbe;

final class ThesaurusFileProbe implements IFileProbe
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
        return preg_match("/^cda[_-]thesaurus/i", basename($filepath)) !== 0;
    }
}
