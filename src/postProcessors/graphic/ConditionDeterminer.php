<?php

namespace CranachImport\PostProcessors\Graphic;

require_once 'entities/IBaseItem.php';
require_once 'entities/Graphic.php';
require_once 'postProcessors/IPostProcessor.php';

use CranachImport\Entities\IBaseItem;
use CranachImport\Entities\Graphic;
use CranachImport\PostProcessors\IPostProcessor;


class ConditionDeterminer implements IPostProcessor {

	private static $conditionMapping = [
		'/^I.\s*Zustand/' => 1,
		'/^II.\s*Zustand/' => 2,
		'/^III.\s*Zustand/' => 3,
	];
	private $isDone = false;


	function __construct() {
	}


	function postProcessItem(IBaseItem $item): IBaseItem {
		if (!($item instanceof Graphic)) {
			throw new \Exception('Pushed item is not of expected class \'Graphic\'');
		}

		$conditionLevel = $this->getConditionLevel($item);

		$item->setConditionLevel($conditionLevel);

		return $item;
	}


	function isDone(): bool {
		return $this->isDone;
	}


	function done() {
		$this->isDone = true;
	}


	private function getConditionLevel(Graphic $graphic): int {
		$conditionLevel = 0;

		$classification = $graphic->getClassification();

		if (is_null($classification)) {
			return $conditionLevel;
		}

		$condition = trim($classification->getCondition());

		foreach(self::$conditionMapping as $conditionPattern => $conditionPatternLevel) {
			if (preg_match($conditionPattern, $condition) === 1) {
				$conditionLevel = $conditionPatternLevel;
				break;
			}
		}

		return $conditionLevel;
	}

}