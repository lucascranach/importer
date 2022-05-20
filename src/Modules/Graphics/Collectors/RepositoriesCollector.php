<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Collectors;

use Error;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicInfo;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Pipeline\Consumer;

class RepositoriesCollector extends Consumer
{
    private $graphicInfos = [
        Language::DE => [],
        Language::EN => [],
    ];


    private function __construct()
    {
    }


    public static function new(

    ): self {
        return new self;
    }


    public function getAllGraphicInfos(): array
    {
        return $this->graphicInfos;
    }

    public function handleItem($item): bool
    {
        if (!($item instanceof GraphicInfo)) {
            echo get_class($item);
            throw new Error('Pushed item is not of the expected class \'GraphicInfo\'');
        }

        $metadata = $item->getMetadata();

        if (is_null($metadata)) {
            return false;
        }

        $langCode = $metadata->getLangCode();
        $inventoryNumber = $item->getInventoryNumber();

        $this->graphicInfos[$langCode][$inventoryNumber] = $item;

        return true;
    }


    public function getRepositories(string $langCode, string $inventoryNumber): ?array
    {
        if (!isset($this->graphicInfos[$langCode][$inventoryNumber])) {
            return null;
        }

        $repositories = [];

        /** @var GraphicInfo */
        $item = $this->graphicInfos[$langCode][$inventoryNumber];

        foreach ($item->getReprintReferences() as $reprintReference) {
            $reprintInventoryNumber = $reprintReference->getInventoryNumber();

            if (!isset($this->graphicInfos[$langCode][$reprintInventoryNumber])) {
                continue;
            }

            $reprintItem = $this->graphicInfos[$langCode][$reprintInventoryNumber];
            $repositories[] = $reprintItem->getRepository();
        }

        return $repositories;
    }


    public function error($error)
    {
        echo get_class($this) . ": Error -> " . $error . "\n";
    }


    public function done(ProducerInterface $producer)
    {
        /* should never trigger an action on done */
    }


    public function cleanUp()
    {
        $this->graphicInfos = [];
    }
}
