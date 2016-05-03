<?php

namespace atoum\telemetry\providers;

use atoum\telemetry;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\EventListener\LogListener;

class log implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['logger'] = function () use ($app) {
			return $app['monolog'];
		};

		$app['monolog'] = $app->share(function (telemetry\application $app) {
			$log = new Logger(isset($app['monolog.name']) ? $app['monolog.name'] : 'telemetry');

			$log->pushHandler($app['monolog.handler']);

			if ($app['debug'] && isset($app['monolog.handler.debug'])) {
				$log->pushHandler($app['monolog.handler.debug']);
			}

			return $log;
		});

		$app['monolog.handler'] = function () use ($app) {
			$level = self::translateLevel($app['monolog.level']);

			return new StreamHandler(
				isset($app['monolog.logfile']) ? $app['monolog.logfile'] : 'php://stdout',
				$level,
				isset($app['monolog.bubble']) ? $app['monolog.bubble'] : true,
				isset($app['monolog.permission']) ? $app['monolog.permission'] : null
			);
		};

		$app['monolog.level'] = function () {
			return Logger::DEBUG;
		};

		$app['monolog.listener'] = $app->share(function (telemetry\application $app) {
			return new LogListener($app['logger']);
		});
	}

	public function boot(Application $app)
	{
		if (isset($app['monolog.listener'])) {
			$app['dispatcher']->addSubscriber($app['monolog.listener']);
		}
	}

	public static function translateLevel($name)
	{
		if (is_int($name))
		{
			return $name;
		}

		$levels = Logger::getLevels();
		$upper = strtoupper($name);

		if (!isset($levels[$upper]))
		{
			throw new \InvalidArgumentException("Provided logging level '$name' does not exist. Must be a valid monolog logging level.");
		}

		return $levels[$upper];
	}
}
