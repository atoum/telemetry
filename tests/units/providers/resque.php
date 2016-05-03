<?php

namespace atoum\telemetry\tests\units\providers;

use atoum\telemetry\resque\broker;
use atoum\telemetry\resque\worker;
use mageekguy\atoum;
use Silex\ServiceProviderInterface;

require_once __DIR__ . '/../../mocks/resque.php';

class resque extends atoum\test
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
					->call('offsetSet')->withArguments('broker')->once
					->call('offsetSet')->withArguments('worker')->once
					->call('offsetSet')->withArguments('resque.broker')->once
					->call('offsetSet')->withArguments('resque.worker')->once
		;
	}

	public function testGetBroker()
	{
		$this
			->given($application = new \mock\atoum\telemetry\application())
			->if($this->newTestedInstance())
			->when($this->testedInstance->register($application))
			->then
				->object($application['broker'])
					->isInstanceOf(broker::class)
					->isIdenticalTo($application['resque.broker'])
				->string(\resque::getBackend())->isEqualTo(sprintf('%s:%d', 'localhost', 6379))
				->mock($application)
					->call('offsetExists')->withArguments('redis.host')->once
					->call('offsetGet')->withArguments('redis.host')->never
					->call('offsetExists')->withArguments('redis.port')->once
					->call('offsetGet')->withArguments('redis.port')->never
					->call('offsetExists')->withArguments('resque.queue')->once
					->call('offsetGet')->withArguments('resque.queue')->never
			->given($application = new \mock\atoum\telemetry\application())
			->if(
				$application['redis.host'] = $host = uniqid(),
				$application['redis.port'] = $port = rand(0, PHP_INT_MAX),
				$application['resque.queue'] = uniqid(),
				$this->testedInstance->register($application)
			)
			->when($application['broker'])
			->then
				->string(\resque::getBackend())->isEqualTo(sprintf('%s:%d', $host, $port))
				->mock($application)
					->call('offsetExists')->withArguments('redis.host')->once
					->call('offsetGet')->withArguments('redis.host')->once
					->call('offsetExists')->withArguments('redis.port')->once
					->call('offsetGet')->withArguments('redis.port')->once
					->call('offsetExists')->withArguments('resque.queue')->once
					->call('offsetGet')->withArguments('resque.queue')->once
		;
	}

	public function testGetWorker()
	{
		$this
			->given($application = new \mock\atoum\telemetry\application())
			->if($this->newTestedInstance())
			->when($this->testedInstance->register($application))
			->then
				->object($application['worker'])
					->isInstanceOf(worker::class)
					->isIdenticalTo($application['resque.worker'])
				->string(\resque::getBackend())->isEqualTo(sprintf('%s:%d', 'localhost', 6379))
				->mock($application)
					->call('offsetExists')->withArguments('redis.host')->once
					->call('offsetGet')->withArguments('redis.host')->never
					->call('offsetExists')->withArguments('redis.port')->once
					->call('offsetGet')->withArguments('redis.port')->never
					->call('offsetExists')->withArguments('resque.queue')->once
					->call('offsetGet')->withArguments('resque.queue')->never
			->given($application = new \mock\atoum\telemetry\application())
			->if(
				$application['redis.host'] = $host = uniqid(),
				$application['redis.port'] = $port = rand(0, PHP_INT_MAX),
				$application['resque.queue'] = uniqid(),
				$this->testedInstance->register($application)
			)
			->when($worker = $application['worker'])
			->then
				->string(\resque::getBackend())->isEqualTo(sprintf('%s:%d', $host, $port))
				->mock($application)
					->call('offsetExists')->withArguments('redis.host')->once
					->call('offsetGet')->withArguments('redis.host')->once
					->call('offsetExists')->withArguments('redis.port')->once
					->call('offsetGet')->withArguments('redis.port')->once
					->call('offsetExists')->withArguments('resque.queue')->once
					->call('offsetGet')->withArguments('resque.queue')->once
		;
	}
}
