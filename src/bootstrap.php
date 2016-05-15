<?php

namespace atoum\telemetry;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new application();

$app['auth_token'] = getenv('ATOUM_TELEMETRY_AUTH_TOKEN');

$app['monolog.name'] = 'atoum-telemetry';
$app['monolog.logfile'] = 'php://stdout';

$app['console.name'] = $app['monolog.name'];
$app['console.version'] = '1.0.0';
$app['console.project_directory'] = __DIR__;

$app['influxdb.host'] = getenv('ATOUM_TELEMETRY_INFLUXDB_HOST') ?: null;
$app['influxdb.port'] = getenv('ATOUM_TELEMETRY_INFLUXDB_PORT') ?: null;
$app['influxdb.database'] = getenv('ATOUM_TELEMETRY_INFLUXDB_DATABASE') ?: null;
$app['influxdb.username'] = getenv('ATOUM_TELEMETRY_INFLUXDB_USERNAME') ?: null;
$app['influxdb.password'] = getenv('ATOUM_TELEMETRY_INFLUXDB_PASSWORD') ?: null;

$app['redis.host'] = getenv('ATOUM_TELEMETRY_REDIS_HOST') ?: null;
$app['redis.port'] = getenv('ATOUM_TELEMETRY_REDIS_PORT') ?: null;
$app['resque.queue'] = getenv('ATOUM_TELEMETRY_RESQUE_QUEUE') ?: null;

$app->boot();

return $app;
