<?php

namespace CranachImport\Modules\Graphics\Operations;

require_once 'interfaces/entities/IBaseItem.php';
require_once 'interfaces/postProcessors/IOperation.php';
require_once 'modules/graphics/entities/Graphic.php';

use CranachImport\Interfaces\Entities\IBaseItem;
use CranachImport\Interfaces\PostProcessors\IOperation;
use CranachImport\Modules\Graphics\Entities\Graphic;


class ConditionDeterminer implements IOperation {

	private static $conditionLangMappings = [
		'de' => [
			[
				'patterns' => [
					'/^I\.\s*zustand/i',
					'/^1\.\s*auflage/i',
				],
				'value' =>  1,
			],
			[
				'patterns' => [
					'/^II\.\s*zustand/i',
					'/^2\.\s*auflage/i',
				],
				'value' => 2,
			],
			[
				'patterns' => [
					'/^III\.\s*zustand/i',
					'/^3\.\s*auflage/i',
				],
				'value' => 3,
			],
		],
		'en' => [
			[
				'patterns' => [
					'/^1st\s*state/i',
					'/^1st\s*edition/i',
				],
				'value' =>  1,
			],
			[
				'patterns' => [
					'/^2nd\s*state/i',
					'/^2nd\s*edition/i',
				],
				'value' => 2,
			],
			[
				'patterns' => [
					'/^3rd\s*state/i',
					'/^3rd\s*edition/i',
				],
				'value' => 3,
			],
		],
	];
	private $conditionLevelCache = [];
	private $isDone = false;


	function __construct() {
	}


	function handleItem(IBaseItem $item): IBaseItem {
		if (!($item instanceof Graphic)) {
			throw new \Exception('Pushed item is not of expected class \'Graphic\'');
		}

		$inventoryNumber = $item->getInventoryNumber();

		if (!isset($this->conditionLevelCache[$inventoryNumber])) {
			$this->conditionLevelCache[$inventoryNumber] = $this->getConditionLevel(
				$item,
				$item->getConditionLevel(),
			);
		}

		$item->setConditionLevel($this->conditionLevelCache[$inventoryNumber]);

		return $item;
	}


	function isDone(): bool {
		return $this->isDone;
	}


	function done() {
		$this->isDone = true;
	}


	private function getConditionLevel(Graphic $graphic, $conditionLevel = 0): int {
		$classification = $graphic->getClassification();

		if (
			is_null($classification)
			|| !isset(self::$conditionLangMappings[$graphic->getLangCode()])
		) {
			return $conditionLevel;
		}

		$condition = trim($classification->getCondition());
		$conditionMappings = self::$conditionLangMappings[$graphic->getLangCode()];

		foreach ($conditionMappings as $conditionMapping) {
			foreach ($conditionMapping['patterns'] as $pattern) {
				if (preg_match($pattern, $condition) === 1) {
					return $conditionMapping['value'];
				}
			}
		}

		return $conditionLevel;
	}

}