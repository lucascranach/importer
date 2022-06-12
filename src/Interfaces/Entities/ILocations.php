<?php

namespace CranachDigitalArchive\Importer\Interfaces\Entities;

/**
 * Representing an entity with localizations
 */
interface ILocations
{
    public function getLocations(): array;
    public function setLocations(array $locations);
}
