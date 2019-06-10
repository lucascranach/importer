<?php

namespace CranachImport\Collectors;

require_once 'entities/IBaseItem.php';

use CranachImport\Entities\IBaseItem;


interface ICollector {

	function addItem(IBaseItem $item);

	function getItems(): array;

}