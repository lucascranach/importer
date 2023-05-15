<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use Error;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Graphics\Collectors\LocationsCollector;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicLanguageCollection;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class ExtenderWithLocations extends Hybrid
{
    private $locationsCollector;
    private $skipNonVirtuals = false;

    private function __construct(LocationsCollector $locationsCollector)
    {
        $this->locationsCollector = $locationsCollector;
    }


    public static function new(LocationsCollector $locationsCollector, bool $skipNonVirtuals = false): self
    {
        $transformer = new self($locationsCollector);

        $transformer->skipNonVirtuals = $skipNonVirtuals;

        return $transformer;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof GraphicLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'GraphicLanguageCollection\'');
        }

        /* Skip non-virtual items*/
        if ($this->skipNonVirtuals && !$item->getIsVirtual()) {
            $this->next($item);
            return true;
        }

        foreach ($item as $langCode => $graphic) {
            $locations = $this->locationsCollector->getLocations($langCode, $item->getInventoryNumber());

            if (!is_null($locations)) {
                $graphic->setLocations($locations);
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
    }
}
