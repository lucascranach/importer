<?php

namespace CranachDigitalArchive\Importer;

require_once __DIR__ . '/vendor/autoload.php';

use CranachDigitalArchive\Importer\Pipeline\Pipeline;

use CranachDigitalArchive\Importer\Modules\Graphics\Exporters\GraphicsJSONLangExistenceTypeExporter;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\{ConditionDeterminer, RemoteImageExistenceChecker};
use CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML\GraphicsLoader;

use CranachDigitalArchive\Importer\Modules\GraphicRestorations\Loaders\XML\GraphicRestorationsLoader;
use CranachDigitalArchive\Importer\Modules\GraphicRestorations\Exporters\GraphicRestorationsJSONExporter;

$graphicsLoader = GraphicsLoader::withSourceAt('./input/20191122/CDA-GR_Datenuebersicht_20191122.xml');
$remoteImageExistenceChecker = RemoteImageExistenceChecker::withCacheAt('./.cache');
$conditionDeterminer = ConditionDeterminer::new();
$graphicsDestination = GraphicsJSONLangExistenceTypeExporter::withDestinationAt('./output/20191122/cda-graphics-v2.json');

$graphicsLoader->pipe(
	$remoteImageExistenceChecker,
	$conditionDeterminer,
	$graphicsDestination,
);

Pipeline::new()->withNodes(
	$graphicsLoader,
	$remoteImageExistenceChecker,
	$conditionDeterminer,
	$graphicsDestination,
)->start();



$graphicRestorationsLoader = GraphicRestorationsLoader::withSourceAt('./input/20191122/CDA-GR_RestDokumente_20191122.xml');
$graphicRestorationsDestination = GraphicRestorationsJSONExporter::withDestinationAt('./output/20191122/cda-graphics-restoration-v2.json');

$graphicRestorationsLoader->pipe(
	$graphicRestorationsDestination,
);

Pipeline::new()->withNodes(
	$graphicRestorationsLoader,
	$graphicRestorationsDestination,
)->start();
