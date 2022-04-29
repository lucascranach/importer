<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Graphics\Collectors\LocationsCollector;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use Error;

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
        if (!($item instanceof Graphic)) {
            throw new Error('Pushed item is not of expected class \'Graphic\'');
        }

        /* Skip non-virtual items*/
        if ($this->skipNonVirtuals && !$item->getIsVirtual()) {
            $this->next($item);
            return true;
        }

        $metadata = $item->getMetadata();

        if (is_null($metadata)) {
            $this->next($item);
            return true;
        }

        $metadata->getLangCode();

        $locations = $this->locationsCollector->getLocations($metadata->getLangCode(), $item->getInventoryNumber());

        if (!is_null($locations)) {
            $item->setLocations($locations);
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
    }
}
