<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search\SearchableGraphic;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Graphics\Collectors\RepositoriesCollector;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use Error;

class ExtenderWithRepositories extends Hybrid
{
    private $repositoriesCollector;
    private $skipNonVirtuals = false;

    private function __construct(RepositoriesCollector $repositoriesCollector)
    {
        $this->repositoriesCollector = $repositoriesCollector;
    }


    public static function new(RepositoriesCollector $repositoriesCollector, bool $skipNonVirtuals = false): self
    {
        $transformer = new self($repositoriesCollector);

        $transformer->skipNonVirtuals = $skipNonVirtuals;

        return $transformer;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof SearchableGraphic)) {
            throw new Error('Pushed item is not of expected class \'SearchableGraphic\'');
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

        $repositories = $this->repositoriesCollector->getRepositories($metadata->getLangCode(), $item->getInventoryNumber());

        if (!is_null($repositories)) {
            $item->setChildRepositories($repositories);
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
