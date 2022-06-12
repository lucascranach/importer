<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaReference;

/**
 * Representing a single meta location reference
 */
class MetaLocationReference extends MetaReference
{
    public $geoPosition = null;


    public function __construct()
    {
        parent::__construct();
    }


    public function setGeoPosition(string $lat, string $lng)
    {
        $this->geoPosition = [
            'lat' => $lat,
            'lng' => $lng,
        ];
    }
}
