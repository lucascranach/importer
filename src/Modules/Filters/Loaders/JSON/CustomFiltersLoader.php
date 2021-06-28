<?php

namespace CranachDigitalArchive\Importer\Modules\Filters\Loaders\JSON;

use Error;
use CranachDigitalArchive\Importer\Interfaces\Loaders\IFileLoader;
use CranachDigitalArchive\Importer\Pipeline\Producer;
use CranachDigitalArchive\Importer\Modules\Filters\Inflators\JSON\CustomFiltersInflator;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\CustomFilter;

/**
 * Custom filters loader on a json file base
 */
class CustomFiltersLoader extends Producer implements IFileLoader
{
    private $sourceFilePath = '';

    private function __construct()
    {
    }


    /**
     * @return self
     */
    public static function withSourceAt(string $sourceFilePath)
    {
        $loader = new self;

        $loader->sourceFilePath = $sourceFilePath;

        if (!file_exists($sourceFilePath)) {
            throw new Error('CustomFilter source file does not exist: ' . $sourceFilePath);
        }

        return $loader;
    }


    /**
     * @return void
     */
    public function run()
    {
        echo 'Processing custom filters file : ' . $this->sourceFilePath . "\n";

        $customFiltersContentRaw = file_get_contents($this->sourceFilePath);
        $customFiltersContent = json_decode($customFiltersContentRaw, true);

        foreach ($customFiltersContent as $customFilterCategoryItem) {
            $customfilterItem = new CustomFilter();
            CustomFiltersInflator::inflate($customFilterCategoryItem, $customfilterItem);
            $this->next($customfilterItem);
        }

        /* Signaling that we are done reading in the xml */
        $this->notifyDone();
    }
}
