<?php

namespace CranachImport\Importers;

require_once 'process/IPipeline.php';

use CranachImport\Process\IPipeline;


/**
 * Interface describing a simple importer
 */
interface IImporter {

	/**
	 * Binding a pipeline to the importer for item handling
	 *
	 * @param IPipeline $pipeline The pipeline instance
	 */
	function registerPipeline(IPipeline $pipeline);

	/**
	 * Triggering the data reading / fetching and import
	 */
	function start();

}