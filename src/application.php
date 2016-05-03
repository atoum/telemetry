<?php

namespace atoum\telemetry;

use atoum\telemetry\controllers\hook;
use atoum\telemetry\controllers\telemetry;
use atoum\telemetry\providers\influxdb;
use atoum\telemetry\providers\log;
use atoum\telemetry\providers\resque;
use Silex;
use Symfony\Component\HttpFoundation\Request;

class application extends Silex\Application
{
	public function boot()
	{
		$this
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
