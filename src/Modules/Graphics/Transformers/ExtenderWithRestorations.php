<?php


namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use Error;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicLanguageCollection;
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
        if (!($item instanceof GraphicLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'GraphicLanguageCollection\'');
        }

        foreach ($item as $langCode => $graphic) {
            $restoration = $this->restorationMemoryExporter->findByFields([
                'inventoryNumber' => $graphic->getInventoryNumber(),
                'langCode' => $langCode,
            ]);

            if (!is_null($restoration) && $restoration instanceof Restoration) {
                $graphic->setRestorationSurveys($restoration->getSurveys());
            }
        }

        $this->next($item);
        return true;
    }


    /**
     * @return void
     */
    public function done(ProducerInterface $producer)
    {
        parent::done($producer);
        $this->cleanUp();
    }


    private function cleanUp(): void
    {
        $this->restorationMemoryExporter = null;
    }
}
