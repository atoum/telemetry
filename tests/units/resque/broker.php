<?php

namespace atoum\telemetry\tests\units\resque;

use mageekguy\atoum;
use atoum\telemetry;

require_once __DIR__ . '/../../mocks/resque.php';

class broker extends atoum\test
{
	public function testClass()
	{
		$this
			->testedClass
				->extends(telemetry\resque::class)
		;
	}

	public function testEnqueue()
	{
		$this
			->given(
				$queue = uniqid(),
				$class = uniqid()
			)
			->if($this->newTestedInstance(uniqid(), rand(0, PHP_INT_MAX), $queue))
			->when($this->testedInstance->enqueue($class))
			->then
				->array(\resque::getLastEnqueued())->isIdenticalTo([$queue, $class, null, false])
			->given($args = [uniqid() => uniqid()])
			->when($this->testedInstance->enqueue($class, $args))
			->then
				->array(\resque::getLastEnqueued())->isIdenticalTo([$queue, $class, $args, false])
		;
	}
}
