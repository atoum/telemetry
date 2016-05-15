<?php

use atoum\telemetry\commands\patch\apply;
use atoum\telemetry\commands\patch\history;

$app = include __DIR__ . '/../src/bootstrap.php';
$console = $app['console'];

$console->add(new apply($app['database'], $app['logger']));
$console->add(new history($app['database'], $app['logger']));

set_time_limit(0);
ini_set('memory_limit', -1);

$console->run();
