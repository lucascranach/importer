<?php

namespace CranachImport\Jobs;

require_once 'IJob.php';


/**
 * Interface describing a concrete file import job
 */
interface IFileJob extends IJob {

	function __construct(string $sourceFilePath);

}