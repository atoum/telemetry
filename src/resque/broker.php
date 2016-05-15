<?php

namespace atoum\telemetry\resque;

use atoum\telemetry\resque;

class broker extends resque
{
	public function enqueue($class, array $args = null, $trackStatus = false) : string
	{
		return \Resque::enqueue($this->queue, $class, $args, $trackStatus);
	}

	public function redis()
	{
		return \Resque::redis();
	}
}
