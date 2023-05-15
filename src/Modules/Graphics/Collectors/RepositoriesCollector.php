<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Collectors;

use Error;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicInfo;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicInfoLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

class RepositoriesCollector extends Consumer
{
    private $graphicInfosCollections = [];


    private function __construct()
    {
    }


    public static function new(

    ): self {
        return new self;
    }


    public function getAllGraphicInfos(): array
    {
        return $this->graphicInfosCollections;
    }

    public function handleItem($item): bool
    {
        if (!($item instanceof GraphicInfoLanguageCollection)) {
            echo get_class($item);
            throw new Error('Pushed item is not of the expected class \'GraphicInfoLanguageCollection\'');
        }

        foreach ($item as $graphicInfo) {
            $this->graphicInfosCollections[$graphicInfo->getInventoryNumber()] = $item;
        }

        return true;
    }


    public function getRepositories(string $langCode, string $inventoryNumber): ?array
    {
        if (!isset($this->graphicInfosCollections[$inventoryNumber])) {
            return null;
        }

        $repositories = [];

        /** @var GraphicInfo */
        $item = $this->graphicInfosCollections[$inventoryNumber]->get($langCode);

        foreach ($item->getReprintReferences() as $reprintReference) {
            $reprintInventoryNumber = $reprintReference->getInventoryNumber();

            if (!isset($this->graphicInfosCollections[$reprintInventoryNumber])) {
                continue;
            }

            $reprintItem = $this->graphicInfosCollections[$reprintInventoryNumber]->get($langCode);
            $repositories[] = $reprintItem->getRepository();
        }

        return $repositories;
    }


    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }


    public function done(IProducer $producer)
    {
        /* should never trigger an action on done */
    }


    public function cleanUp()
    {
        $this->graphicInfosCollections = [];
    }
}
