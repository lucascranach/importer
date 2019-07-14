<?php

namespace CranachImport\Exporters;

require_once 'IFileExporter.php';
require_once 'entities/IBaseItem.php';

use CranachImport\Entities\IBaseItem;


/**
 * Graphics exporter on a json flat file base
 * - one file per language
 * - linked works
 */
class GraphicsJSONLangExistenceTypeExporter implements IFileExporter {

	private $fileExt = 'json';
	private $filename = null;
	private $dirname = null;
	private $destFilepaths = [];
	private $langBuckets = [];
	private $done = false;
	private $inventoryNumberList = [];


	function __construct(string $destFilepath) {
		$filename = basename($destFilepath);
		$this->dirname = trim(dirname($destFilepath));

		$splitFilename = array_map('trim', explode('.', $filename));

		if (count($splitFilename) === 2 && strlen($splitFilename[1])) {
			$this->fileExt = $splitFilename[1];
		}

		$this->filename = $splitFilename[0];
	}


	function pushItem(IBaseItem $item) {
		if ($this->isDone()) {
			throw new \Error('Can\'t push more items after done() was called!');
		}

		if (!isset($this->langBuckets[$item->getLangCode()])) {
			$this->langBuckets[$item->getLangCode()] = (object) [
				'items' => [],
			];
		}

		$this->inventoryNumberList[] = $item->getInventoryNumber();
		$this->langBuckets[$item->getLangCode()]->items[] = $item;
	}


	function isDone(): bool {
		return $this->done;
	}


	function outputReferenceCheckResult() {
		if (count($this->langBuckets) === 0) {
			throw new Exception('At least one language needed!');
		}

		$firstLangBucket = $this->langBuckets[array_key_first($this->langBuckets)];
		$objectsWithMissingReferencesList = [];

		foreach($firstLangBucket->items as $item) {
			foreach ($item->getReferences() as $reference) {
				if (!in_array($reference->getInventoryNumber(), $this->inventoryNumberList)) {
					$objectsWithMissingReferencesList[] = $item->getInventoryNumber();
				}
			}
		}

		echo "\nGraphics with missing references: \n\n";

		if (count($objectsWithMissingReferencesList) > 0) {
			foreach ($objectsWithMissingReferencesList as $objectInventoryNumber) {
				echo "    - " . $$objectInventoryNumber . "\n";
			}
		} else {
			echo "    - No missing references!\n\n";
		}
	}


	function done() {
		if (is_null($this->dirname) || empty($this->dirname)
	     || is_null($this->filename) || empty($this->filename)) {
			throw new \Error('No filepath for JSON graphics export set!');
		}

		$this->outputReferenceCheckResult();

		foreach ($this->langBuckets as $langCode => $bucket) {
			$existenceTypes = array_reduce(
				$bucket->items,
				function($carry, $item) {
					$existenceTypeKey = $item->getIsVirtual() ? 'virtual' : 'real';
					$carry[$existenceTypeKey][] = $item;
					return $carry;
				},
				[ "virtual" => [], "real" => [] ],
			);

			foreach ($existenceTypes as $existenceTypeKey => $existenceTypeItems) {
				$filename = implode('.', [
					$this->filename,
					$existenceTypeKey,
					$langCode,
					$this->fileExt,
				]);
				$destFilepath = $this->dirname . DIRECTORY_SEPARATOR . $filename;

				$data = json_encode(array('items' => $existenceTypeItems), JSON_PRETTY_PRINT);
				file_put_contents($destFilepath, $data);
			}
		}

		$this->done = true;
	}

}