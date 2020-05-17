<?php

namespace CranachDigitalArchive\Importer;

require_once __DIR__ . '/vendor/autoload.php';

use CranachDigitalArchive\Importer\Modules\Graphics\Exporters\GraphicsJSONLangExistenceTypeExporter;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\{ConditionDeterminer, RemoteImageExistenceChecker};
use CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML\GraphicsLoader;
use CranachDigitalArchive\Importer\Pipeline\Pipeline;

$graphicsLoader = GraphicsLoader::withSourceAt('./input/20191122/CDA-GR_Datenuebersicht_20191122.xml');
$remoteImageExistenceChecker = RemoteImageExistenceChecker::withCacheAt('./.cache');
$conditionDeterminer = ConditionDeterminer::new();
$destination = GraphicsJSONLangExistenceTypeExporter::withDestinationAt('./output/20191122/cda-graphics-v2.json');

$graphicsLoader->pipe(
	$remoteImageExistenceChecker,
	$conditionDeterminer,
	$destination,
);

Pipeline::new()->withNodes(
	$graphicsLoader,
	$remoteImageExistenceChecker,
	$conditionDeterminer,
	$destination,
)->start();
