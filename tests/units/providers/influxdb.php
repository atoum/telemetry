<?php

namespace atoum\telemetry\tests\units\providers;

use atoum\telemetry\resque\broker;
use atoum\telemetry\resque\worker;
use InfluxDB\Database;
use mageekguy\atoum;
use Silex\ServiceProviderInterface;

require_once __DIR__ . '/../../mocks/resque.php';

class influxdb extends atoum\test
{
	public function testClass()
	{
		$this
			->testedClass
				->extends(ServiceProviderInterface::class)
		;
	}

	public function testRegister()
	{
		$this
			->given($application = new \mock\atoum\telemetry\application())
			->if($this->newTestedInstance())
			->when($this->testedInstance->register($application))
			->then
				->mock($application)
					->call('offsetSet')->withArguments('database')->once
					->call('offsetSet')->withArguments('influxdb')->once
					->call('offsetSet')->withArguments('influxdb.client')->once
		;
	}

	public function testGetDatabase()
	{
		$this
			->given($application = new \mock\atoum\telemetry\application())
			->if($this->newTestedInstance())
			->when($this->testedInstance->register($application))
			->then
				->object($application['database'])
					->isInstanceOf(Database::class)
					->isIdenticalTo($application['influxdb'])
				->mock($application)
					->call('offsetExists')->withArguments('influxdb.host')->once
					->call('offsetGet')->withArguments('influxdb.host')->never
					->call('offsetExists')->withArguments('influxdb.port')->once
					->call('offsetGet')->withArguments('influxdb.port')->never
					->call('offsetExists')->withArguments('influxdb.database')->once
					->call('offsetGet')->withArguments('influxdb.database')->never
					->call('offsetExists')->withArguments('influxdb.username')->once
					->call('offsetGet')->withArguments('influxdb.username')->never
					->call('offsetExists')->withArguments('influxdb.password')->once
					->call('offsetGet')->withArguments('influxdb.password')->never
					->call('offsetGet')->withArguments('influxdb.client')->once
			->given($application = new \mock\atoum\telemetry\application())
			->if(
				$application['influxdb.host'] = $host = uniqid(),
				$application['influxdb.port'] = $port = rand(1, 1024),
				$application['influxdb.database'] = uniqid(),
				$application['influxdb.username'] = uniqid(),
				$application['influxdb.password'] = uniqid(),
				$this->testedInstance->register($application)
			)
			->when($application['database'])
			->then
				->mock($application)
					->call('offsetExists')->withArguments('influxdb.host')->once
					->call('offsetGet')->withArguments('influxdb.host')->once
					->call('offsetExists')->withArguments('influxdb.port')->once
					->call('offsetGet')->withArguments('influxdb.port')->once
					->call('offsetExists')->withArguments('influxdb.database')->once
					->call('offsetGet')->withArguments('influxdb.database')->once
					->call('offsetExists')->withArguments('influxdb.username')->once
					->call('offsetGet')->withArguments('influxdb.username')->once
					->call('offsetExists')->withArguments('influxdb.password')->once
					->call('offsetGet')->withArguments('influxdb.password')->once
					->call('offsetGet')->withArguments('influxdb.client')->once
		;
	}
}
