<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Transformers;

use Error;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExcludeByInventoryNumberPrefix extends Hybrid
{
    private string $prefix = '';
    private string $typeDescription = '';
    private int $sampleLimit = 25;

    private int $skippedCount = 0;
    private array $skippedInventoryNumbersSample = [];

    private function __construct()
    {
    }

    public static function new(string $prefix, string $typeDescription = '', int $sampleLimit = 25): self
    {
        $transformer = new self();
        $transformer->prefix = $prefix;
        $transformer->typeDescription = $typeDescription;
        $transformer->sampleLimit = $sampleLimit;

        return $transformer;
    }

    public function handleItem($item): bool
    {
        if (!is_object($item) || !method_exists($item, 'getInventoryNumber')) {
            throw new Error('Pushed item expected to provide method \'getInventoryNumber\'');
        }

        $inventoryNumber = trim(strval($item->getInventoryNumber()));

        if ($inventoryNumber !== '' && str_starts_with($inventoryNumber, $this->prefix)) {
            $this->skippedCount++;

            if (count($this->skippedInventoryNumbersSample) < $this->sampleLimit) {
                $this->skippedInventoryNumbersSample[] = $inventoryNumber;
            }

            return true;
        }

        $this->next($item);
        return true;
    }

    public function done(IProducer $producer)
    {
        if ($this->skippedCount > 0) {
            $typeDescriptionStr = (!empty($this->typeDescription)) ? ' (' . $this->typeDescription . ')' : '';
            echo "\n  Skipped artefacts by inventory prefix '" . $this->prefix . "'" . $typeDescriptionStr . ": " . $this->skippedCount . "\n";

            foreach ($this->skippedInventoryNumbersSample as $inventoryNumber) {
                echo '      - ' . $inventoryNumber . "\n";
            }

            if ($this->skippedCount > count($this->skippedInventoryNumbersSample)) {
                echo "      - ...\n";
            }

            echo "\n";
        }

        $this->skippedCount = 0;
        $this->skippedInventoryNumbersSample = [];

        parent::done($producer);
    }
}
