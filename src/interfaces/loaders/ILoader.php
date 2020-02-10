<?php

namespace CranachImport\Interfaces\Loaders;



/**
 * Interface describing a simple import loader
 */
interface ILoader {

	/**
	 * Binding a pipeline to the importer for item handling
	 *
	 * @param IPipeline $pipeline The pipeline instance
	 */
	// function registerPipeline(IPipeline $pipeline);

	/**
	 * Start the job and trigger the data reading / fetching and import
	 */
	// function start();

}