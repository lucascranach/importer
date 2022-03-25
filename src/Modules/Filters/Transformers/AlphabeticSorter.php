<?php

namespace CranachDigitalArchive\Importer\Modules\Filters\Transformers;

use CranachDigitalArchive\Importer\Modules\Filters\Entities\Filter;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\LangFilterContainer;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use Error;

class AlphabeticSorter extends Hybrid
{
    private $paths = [
        ['subject'], // Subject
        ['subject','010405'], // Subject > Classical Mythology and Ancient History
        ['subject', '010402', '01040201', '0104020102'], // Portraits > Male > Nobility
        ['subject', '010402', '01040201', '0104020101'], // Portraits > Male > Public Personalities
        ['subject', '010402', '01040202', '0104020202'], // Portraits > Female > Nobility
        ['subject', '010402', '01040202', '0104020201'], // Portraits > Female > Public Personalities
    ];

    private $recursivePaths = [
        ['collection_repository', 'collection_repository.country'],
    ];

    private function __construct()
    {
    }


    public static function new(): self
    {
        return new self;
    }

    public function handleItem($item): bool
    {
        if (!($item instanceof LangFilterContainer)) {
            throw new Error('Pushed item is not of expected class \'LangFilterContainer\'');
        }

        $this->next($this->sortFilters($item));
        return true;
    }

    private function sortFilters(LangFilterContainer $container): LangFilterContainer
    {
        foreach ($this->paths as $path) {
            $this->sortItemChildren($container, $path);
        }

        foreach ($this->recursivePaths as $path) {
            $this->sortItemChildrenRecursively($container, $path);
        }

        return $container;
    }


    private function sortItemChildren(LangFilterContainer $container, array $path)
    {
        $matchingFilter = $this->findMatchingFilter($container->getFilter(), $path);

        if (!is_null($matchingFilter)) {
            $matchingFilter->setChildren($this->sortByText($matchingFilter->getChildren()));
        }
    }


    private function sortItemChildrenRecursively(LangFilterContainer $container, array $path)
    {
        $matchingFilter = $this->findMatchingFilter($container->getFilter(), $path);

        if (!is_null($matchingFilter)) {
            $matchingFilter = $this->sortRecursively($matchingFilter);
        }
    }


    private function findMatchingFilter(Filter $filter, array $matchingPath): ?Filter
    {
        if (count($matchingPath) === 0) {
            return $filter;
        }

        $pathItem = array_shift($matchingPath);

        if ($filter->getId() !== $pathItem) {
            return null;
        }

        if (count($matchingPath) > 0) {
            $children = $filter->getChildren();

            if (count($children) === 0) {
                return null;
            }

            $matchingFilter = null;

            foreach ($children as $child) {
                $result = $this->findMatchingFilter($child, $matchingPath);

                if (!is_null($result)) {
                    $matchingFilter = $result;
                }
            }

            return $matchingFilter;
        } else {
            return $filter;
        }
    }

    private function sortRecursively(Filter $filter): Filter
    {
        $filter->setChildren($this->sortByText($filter->getChildren()));

        foreach ($filter->getChildren() as $child) {
            $this->sortRecursively($child);
        }

        return $filter;
    }

    private function sortByText(array $items)
    {
        $toBeReplaced = ['ä', 'Ä', 'ö', 'Ö', 'ü', 'Ü', 'ß'];
        $replacements = ['ae', 'Ae', 'oe', 'Oe', 'ue', 'Ue', 'ss'];

        usort($items, function ($a, $b) use ($toBeReplaced, $replacements) {
            $aText = str_replace($toBeReplaced, $replacements, $a->getText());
            $bText = str_replace($toBeReplaced, $replacements, $b->getText());

            return strcasecmp($aText, $bText);
        });

        return $items;
    }
}
