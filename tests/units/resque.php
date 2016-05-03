<?php

namespace atoum\telemetry\tests\units;

use mageekguy\atoum;

require_once __DIR__ . '/../mocks/resque.php';

class resque extends atoum\test
{
	public function test__construct()
	{
		$this
			->given(
				$host = uniqid(),
				$port = rand(0, PHP_INT_MAX)
			)
			->if($this->newTestedInstance($host, $port, uniqid()))
			->then
				->string(\resque::getBackend())->isEqualTo(sprintf('%s:%d', $host, $port))
		;
	}
}
