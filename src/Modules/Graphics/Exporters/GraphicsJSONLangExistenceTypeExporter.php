<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Exporters;

use Error;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Interfaces\Exporters\IFileExporter;
use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Pipeline\Consumer;


/**
 * Graphics exporter on a json flat file base
 * - one file per language
 * - linked works
 */
class GraphicsJSONLangExistenceTypeExporter extends Consumer implements IFileExporter {

	private $fileExt = 'json';
	private $filename = null;
	private $dirname = null;
	private $langBuckets = [];
	private $inventoryNumberList = [];
	private $done = false;


	private function __construct()
	{
	}


	public static function withDestinationAt(string $destFilepath)
	{
		$exporter = new self;

		$filename = basename($destFilepath);
		$exporter->dirname = trim(dirname($destFilepath));

		$splitFilename = array_map('trim', explode('.', $filename));

		if (count($splitFilename) === 2 && strlen($splitFilename[1])) {
			$exporter->fileExt = $splitFilename[1];
		}

		$exporter->filename = $splitFilename[0];

		return $exporter;
	}


	function handleItem($item): bool
	{
		if (!($item instanceof Graphic)) {
			throw new Error('Pushed item is not of expected class \'Graphic\'');
		}

		if ($this->done) {
			throw new Error('Can\'t push more items after done() was called!');
		}

		if (!isset($this->langBuckets[$item->getLangCode()])) {
			$this->langBuckets[$item->getLangCode()] = (object) [
				'items' => [],
			];
		}

		$this->inventoryNumberList[] = $item->getInventoryNumber();
		$this->langBuckets[$item->getLangCode()]->items[] = $item;

		return true;
	}


	function outputReferenceCheckResult()
	{
		if (count($this->langBuckets) === 0) {
			throw new Error('At least one language needed!');
		}

		$firstLangBucket = $this->langBuckets[array_key_first($this->langBuckets)];
		$objectsWithMissingReferencesList = [];

		foreach($firstLangBucket->items as $item) {
			foreach ($item->getReprintReferences() as $reference) {
				if (!in_array($reference->getInventoryNumber(), $this->inventoryNumberList)) {
					$objectsWithMissingReferencesList[] = $item->getInventoryNumber();
				}
			}

			foreach ($item->getRelatedWorkReferences() as $reference) {
				if (!in_array($reference->getInventoryNumber(), $this->inventoryNumberList)) {
					$objectsWithMissingReferencesList[] = $item->getInventoryNumber();
				}
			}
		}

		echo "\n  Graphics with missing references: \n\n";

		if (count($objectsWithMissingReferencesList) > 0) {
			foreach ($objectsWithMissingReferencesList as $objectInventoryNumber) {
				echo "      - " . $objectInventoryNumber . "\n";
			}
		} else {
			echo "      - No missing references!\n\n";
		}
	}


	function done(ProducerInterface $producer)
	{
		if (is_null($this->dirname) || empty($this->dirname)
	     || is_null($this->filename) || empty($this->filename)) {
			throw new Error('No filepath for JSON graphics export set!');
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

				if(!file_exists($this->dirname)) {
					mkdir($this->dirname, 0777, TRUE);
				}

				file_put_contents($destFilepath, $data);
			}
		}

		$this->done = true;
	}


	public function error($error)
	{
		echo get_class($this) . ": Error -> " . $error . "\n";
	}

}