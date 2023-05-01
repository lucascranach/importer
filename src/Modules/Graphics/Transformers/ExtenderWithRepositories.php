<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search\SearchableGraphic;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Modules\Graphics\Collectors\RepositoriesCollector;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Search\SearchableGraphicLanguageCollection;
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
        if (!($item instanceof SearchableGraphicLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'SearchableGraphicLanguageCollection\'');
        }

        /* Skip non-virtual items*/
        if ($this->skipNonVirtuals && !$item->getIsVirtual()) {
            $this->next($item);
            return true;
        }

        /**
         * @var string $langCode
         * @var \CranachDigitalArchive\Importer\Modules\Graphics\Interfaces\ISearchableGraphic $searchableGraphic
         */
        foreach ($item as $langCode => $searchableGraphic) {
            $repositories = $this->repositoriesCollector->getRepositories($langCode, $item->getInventoryNumber());

            if (!is_null($repositories)) {
                $searchableGraphic->setChildRepositories($repositories);
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
    }
}
