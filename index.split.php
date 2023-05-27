<?php

namespace CranachDigitalArchive\Importer;

use CranachDigitalArchive\Importer\Constructions\Default\Utils\Parameters;
use CranachDigitalArchive\Importer\Constructions\Default\Utils\EnvironmentVariables;
use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;
use CranachDigitalArchive\Importer\Constructions\Default\Init;

ini_set('memory_limit', '2048M');
echo "MemoryLimit: " . ini_get('memory_limit') . "\n\n";

require_once __DIR__ . '/vendor/autoload.php';

$parameters = Parameters::new(EnvironmentVariables::new(__DIR__));
$paths = Paths::new(__DIR__, $parameters);

Init::new($parameters, $paths)
    ->run()
    ->cleanUp();
