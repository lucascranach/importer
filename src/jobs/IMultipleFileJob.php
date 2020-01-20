<?php

namespace CranachImport\Jobs;

require_once 'IJob.php';


/**
 * Interface describing a multiple file import job
 */
interface IMultipleFileJob extends IJob {

	function __construct(array $sourceFilePaths);

}