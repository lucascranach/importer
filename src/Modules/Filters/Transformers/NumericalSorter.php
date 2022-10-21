<?php

namespace CranachDigitalArchive\Importer\Modules\Filters\Transformers;

use CranachDigitalArchive\Importer\Modules\Filters\Entities\Filter;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\LangFilterContainer;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use Error;

class NumericalSorter extends Hybrid
{
    private $paths = [
    ];

    private $recursivePaths = [
        ['subject', '010403', '01040301'], // Subject > Christian Religion / Bible > The Old Testament
        ['subject', '010403', '01040302'], // Subject > Christian Religion / Bible > The New Testament
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
            $matchingFilter->setChildren($this->sortById($matchingFilter->getChildren()));
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
        $filter->setChildren($this->sortById($filter->getChildren()));

        foreach ($filter->getChildren() as $child) {
            $this->sortRecursively($child);
        }

        return $filter;
    }

    private function sortById(array $items)
    {
        usort($items, function ($a, $b) {
            return intval($a->getId()) - intval($b->getId());
        });

        return $items;
    }
}
