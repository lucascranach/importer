<?php


namespace CranachDigitalArchive\Importer\Modules\Drawings\Transformers;

use Error;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\DrawingLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Restorations\Exporters\RestorationsMemoryExporter;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Restoration;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithRestorations extends Hybrid
{
    private $restorationMemoryExporter = null;


    private function __construct()
    {
    }


    public static function new(RestorationsMemoryExporter $restorationMemoryExporter): self
    {
        $transformer = new self;

        $transformer->restorationMemoryExporter = $restorationMemoryExporter;

        return $transformer;
    }

    public function handleItem($item): bool
    {
        if (!($item instanceof DrawingLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'DrawingLanguageCollection\'');
        }

        foreach ($item as $subItem) {
            $metadata = $subItem->getMetadata();
            $langCode = !is_null($metadata) ? $metadata->getLangCode() : 'unknown';

            $restoration = $this->restorationMemoryExporter->findByFields([
                'inventoryNumber' => $subItem->getInventoryNumber(),
                'langCode' => $langCode,
            ]);

            if (!is_null($restoration) && $restoration instanceof Restoration) {
                $subItem->setRestorationSurveys($restoration->getSurveys());
            }
        }

        $this->next($item);
        return true;
    }


    /**
     * @return void
     */
    public function done(IProducer $producer)
    {
        parent::done($producer);
        $this->cleanUp();
    }


    private function cleanUp(): void
    {
        $this->restorationMemoryExporter = null;
    }
}
