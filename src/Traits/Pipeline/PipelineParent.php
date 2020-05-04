<?php

namespace CranachDigitalArchive\Importer\Traits\Pipeline;

use CranachDigitalArchive\Importer\Interfaces\{
    Entities\IBaseItem,
    Pipeline\IPipelineChild,
    Pipeline\IPipelineDestination
};


trait PipelineParent {

	private $children = [];


	function getChildren(): array {
		return $this->children;
	}

	function pipe(IPipelineChild ...$children): ?IPipelineChild {

		if (empty($children)) {
			throw new \Exception('Missing item to pipe to');
		}

		$firstChild = current($children);
		$lastChild = end($children);

		$firstChild->setParent($this);

		$this->children[] = $firstChild;

		foreach($children as $idx => $currChild) {
			if ($currChild === $lastChild) {
				break;
			}

			$nextChild = $children[$idx + 1];
			$isNotLast = $idx < count($children) - 2;

			if (is_subclass_of($nextChild, IPipelineDestination::class) && $isNotLast) {
				throw new \Exception('Item is only allowed to be at the end');
			}

			$currChild->pipe($nextChild);
		}

		return $lastChild;

	}

	function propagate(IBaseItem $item) {
		foreach($this->children as $child) {
			$child->handleItem($item);
		}
	}

	function done() {
		foreach($this->getChildren() as $child) {
			$child->done();
		}
	}

}
