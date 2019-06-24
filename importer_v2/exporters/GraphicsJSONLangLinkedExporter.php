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
class GraphicsJSONLangLinkedExporter implements IFileExporter {

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
		$referenceCheckOutput = false;

		foreach ($this->langBuckets as $langBucket) {

			$objectsWithMissingReferencesList = (object) [
				'firstLevelReferences' => [],
				'secondLevelReferences' => [],
			];

			foreach($langBucket->items as $item) {
				foreach ($item->getFirstLevelReferences() as $reference) {
					if (!in_array($reference->getInventoryNumber(), $this->inventoryNumberList)) {
						$objectsWithMissingReferencesList->firstLevelReferences[] = $item->getInventoryNumber();
					}
				}

				foreach ($item->getSecondLevelReferences() as $reference) {
					$croppedInventoryNumber = preg_replace('/^G_/', '', $reference->getInventoryNumber());

					if (!in_array($croppedInventoryNumber, $this->inventoryNumberList)) {
						$objectsWithMissingReferencesList->secondLevelReferences[] = $item->getInventoryNumber();
					}
				}
			}

			if (!$referenceCheckOutput) {

				echo "Graphics with missing references: \n\n";
				echo "  first level references: \n";
				foreach ($objectsWithMissingReferencesList->firstLevelReferences as $objectInventoryNumber) {
					echo "    - " . $$objectInventoryNumber . "\n";
				}

				echo "\n";

				echo "  second level references: \n";
				foreach ($objectsWithMissingReferencesList->secondLevelReferences as $objectInventoryNumber) {
					echo "    - " . $$objectInventoryNumber . "\n";
				}

				echo "\n";

				$referenceCheckOutput = true;
			}
		}
	}


	function done() {
		if (is_null($this->dirname) || empty($this->dirname)
	     || is_null($this->filename) || empty($this->filename)) {
			throw new \Error('No filepath for JSON graphics export set!');
		}

		$this->outputReferenceCheckResult();

		foreach ($this->langBuckets as $langCode => $bucket) {
			$filename = $this->filename . '.' . $langCode . '.' . $this->fileExt;
			$destFilepath = $this->dirname . DIRECTORY_SEPARATOR . $filename;

			$data = json_encode($bucket, JSON_PRETTY_PRINT);
			file_put_contents($destFilepath, $data);
		}

		$this->done = true;
	}

}