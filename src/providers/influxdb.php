<?php

namespace atoum\telemetry\providers;

use atoum\telemetry;
use InfluxDB;
use Silex\Application;
use Silex\ServiceProviderInterface;

class influxdb implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['database'] = $app->share(function (telemetry\application $app) {
			return $app['influxdb'];
		});

		$app['influxdb'] = $app->share(function (telemetry\application $app) {
			return $app['influxdb.client']->selectDB(isset($app['influxdb.database']) ? $app['influxdb.database'] : 'atoum');
		});

		$app['influxdb.client'] = $app->share(function (telemetry\application $app) {
			return new InfluxDB\Client(
				isset($app['influxdb.host']) ? $app['influxdb.host'] : 'localhost',
				isset($app['influxdb.port']) ? $app['influxdb.port'] : 8086
			);
		});
	}

	public function boot(Application $app)
	{

	}
}
