<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Modules\Locations\Sources\LocationsSource;
use CranachDigitalArchive\Importer\Modules\Main\Collectors\MetaReferenceCollector;

final class Base
{
    private LocationsSource $locationsSource;
    private MetaReferenceCollector $metaReferenceCollector;

    private function __construct(Paths $paths)
    {
        /* Locations */
        $this->locationsSource = LocationsSource::withSourceAt(
            $paths->getResourcesPath('locations.json'),
        );

        /* MetaReferences -> Thesaurus-Links */
        $this->metaReferenceCollector = MetaReferenceCollector::new();


    }

    public static function new(Paths $paths): self
    {
        return new self($paths);
    }

    public function getLocationsSource(): LocationsSource
    {
        return $this->locationsSource;
    }

    public function getMetaReferenceCollector(): MetaReferenceCollector
    {
        return $this->metaReferenceCollector;
    }

    public function run(): self
    {
        /* Currently empty */
        return $this;
    }

    public function cleanUp(): void
    {
        $this->getLocationsSource()->cleanUp();
        $this->getMetaReferenceCollector()->cleanUp();
    }
}
