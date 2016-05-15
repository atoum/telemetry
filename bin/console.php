<?php

use atoum\telemetry\commands\patch\apply;
use atoum\telemetry\commands\patch\history;
use atoum\telemetry\commands\worker\backlog;

$app = include __DIR__ . '/../src/bootstrap.php';
$console = $app['console'];

$console->add(new apply($app['database'], $app['logger']));
$console->add(new history($app['database'], $app['logger']));
$console->add(new backlog($app['broker']));

set_time_limit(0);
ini_set('memory_limit', -1);

$console->run();
