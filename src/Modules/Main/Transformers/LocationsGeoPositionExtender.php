<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Transformers;

use CranachDigitalArchive\Importer\AbstractItemLanguageCollection;
use Error;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use CranachDigitalArchive\Importer\Modules\Locations\Sources\LocationsSource;
use CranachDigitalArchive\Importer\Interfaces\Entities\ILocations;

class LocationsGeoPositionExtender extends Hybrid
{
    /** @var LocationsSource */
    private $locationsSource;

    private function __construct()
    {
    }

    public static function new(LocationsSource $source): self
    {
        $extender = new self();

        $extender->locationsSource = $source;

        return $extender;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof AbstractItemLanguageCollection)) {
            throw new Error('Pushed item does not implement \'AbstractItemLanguageCollection\' interface');
        }

        foreach ($item as $collectionItem) {
            if (!($collectionItem instanceof ILocations)) {
                continue;
            }

            $locations = $collectionItem->getLocations();

            foreach ($locations as $location) {
                if (strlen($location->getURL()) === 0) {
                    continue;
                }

                $foundSourceLocation = $this->locationsSource->getLocationByURL($location->getURL());

                if (is_null($foundSourceLocation)) {
                    continue;
                }

                $location->setGeoPosition(
                    $foundSourceLocation->getLatitude(),
                    $foundSourceLocation->getLongitude(),
                );
            }

            $collectionItem->setLocations($locations);
        }

        $this->next($item);
        return true;
    }
}
