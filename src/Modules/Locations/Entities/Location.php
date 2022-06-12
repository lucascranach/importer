<?php

namespace CranachDigitalArchive\Importer\Modules\Locations\Entities;

class Location
{
    public $name;
    public $latitude;
    public $longitude;

    public function __construct(string $name, string $latitude, string $longitude)
    {
        $this->name = $name;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public static function fromStoredDataset(array $locationRaw): self
    {
        return new self(
            $locationRaw['name'],
            $locationRaw['latitude'],
            $locationRaw['longitude'],
        );
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }
}
