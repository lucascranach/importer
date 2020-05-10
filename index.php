<?php

namespace CranachDigitalArchive\Importer;

require_once __DIR__ . '/vendor/autoload.php';

use CranachDigitalArchive\Importer\Modules\Graphics\Exporters\GraphicsJSONLangExistenceTypeExporter;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\ConditionDeterminer;
use CranachDigitalArchive\Importer\Modules\Graphics\Transformers\RemoteImageExistenceChecker;
use CranachDigitalArchive\Importer\Modules\Graphics\Loaders\XML\GraphicsLoader;

$loader = GraphicsLoader::withSourceAt('./input/20191122/CDA-GR_Datenuebersicht_20191122.xml');
$node = $loader->pipe(
	RemoteImageExistenceChecker::withCacheAt('./.cache'),
	ConditionDeterminer::new(),
);
$node->pipe(GraphicsJSONLangExistenceTypeExporter::withDestinationAt('./output/20191122/cda-graphics-v2.json'));


$loader->run();