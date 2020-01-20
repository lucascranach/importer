<?php

namespace CranachImport\Jobs;

require_once 'process/IPipeline.php';

use CranachImport\Process\IPipeline;


/**
 * Interface describing a simple import job
 */
interface IJob {

	/**
	 * Binding a pipeline to the importer for item handling
	 *
	 * @param IPipeline $pipeline The pipeline instance
	 */
	function registerPipeline(IPipeline $pipeline);

	/**
	 * Start the job and trigger the data reading / fetching and import
	 */
	function start();

}