<?php

namespace atoum\telemetry\tests\units\resque;

use mageekguy\atoum;
use atoum\telemetry;

require_once __DIR__ . '/../../mocks/resque.php';

class worker extends atoum\test
{
	public function testClass()
	{
		$this
			->testedClass
				->extends(telemetry\resque::class)
		;
	}

	public function test__construct()
	{
		$this
			->given(
				$factory = function($queue) use (& $worker) {
					return $worker = new \mock\Resque_Worker($queue);
				},
				$queue = uniqid()
			)
			->if($this->newTestedInstance(uniqid(), rand(0, PHP_INT_MAX), $queue, $factory))
			->then
				->variable($worker->logLevel)->isIdenticalTo(\Resque_Worker::LOG_VERBOSE)
				->array($worker->queues())->isIdenticalTo([$queue])
		;
	}

	public function testConsume()
	{
		$this
			->given(
				$factory = function($queue) use (& $worker) {
					$worker = new \mock\Resque_Worker($queue);

					$this->calling($worker)->work->doesNothing();

					return $worker;
				},
				$queue = uniqid()
			)
			->if($this->newTestedInstance(uniqid(), rand(0, PHP_INT_MAX), $queue, $factory))
			->when($this->testedInstance->consume())
			->then
				->mock($worker)->call('work')->withArguments(5)->once
		;
	}
}
