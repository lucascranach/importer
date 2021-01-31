<?php

namespace CranachDigitalArchive\Importer\Interfaces\Loaders;

/**
 * Interface describing a simple import loader
 */
interface ILoader
{
    /**
     * Start the loader to read / fetch and import data
     */
    public function run();
}
