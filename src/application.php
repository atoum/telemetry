<?php

namespace atoum\telemetry;

use atoum\telemetry\controllers\hook;
use atoum\telemetry\controllers\telemetry;
use atoum\telemetry\providers\influxdb;
use atoum\telemetry\providers\log;
use atoum\telemetry\providers\resque;
use JDesrosiers\Silex\Provider\SwaggerServiceProvider;
use Silex;
use SwaggerUI\Silex\Provider\SwaggerUIServiceProvider;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Info(
 *     title="atoum telemetry",
 *     termsOfServiceUrl="http://helloreverb.com/terms/",
 *     contact="team@atoum.org",
 *     license="BSD-3-Clause",
 *     licenseUrl="https://raw.githubusercontent.com/atoum/telemetry/master/LICENSE"
 * )
 */
class application extends Silex\Application
{
	public function boot()
	{
		$this
			->register(
				new SwaggerServiceProvider(),
				[
					'swagger.apiVersion' => 1,
					'swagger.apiDocPath' => '/docs',
					'swagger.srcDir' => __DIR__ . '/../vendor/zircote/swagger-php/library',
					'swagger.servicePath' => __DIR__ . '/controllers',
				]
			)
			->register(
				new SwaggerUIServiceProvider(),
				[
					'swaggerui.path'       => '/swagger',
					'swaggerui.apiDocPath' => '/docs'
				]
			)
			->register(new log())
			->register(new influxdb())
			->register(new resque())
		;

		parent::boot();
	}

	public function run(Request $request = null)
	{
		$this->post('/hook/{token}', new hook($this['auth_token'], $this['broker']));
		$this->post('/', new telemetry($this['broker']));

		parent::run($request);
	}
}
