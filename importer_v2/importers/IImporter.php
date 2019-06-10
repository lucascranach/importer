<?php

namespace CranachImport\Importers;

require_once 'process/IPipeline.php';

use CranachImport\Process\IPipeline;


interface IImporter {

	function registerPipeline(IPipeline $pipeline);

	function start();

}