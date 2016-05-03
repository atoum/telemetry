<?php

namespace atoum\telemetry\resque;

use atoum\telemetry\resque;
use Resque_Worker;

class worker extends resque
{
	protected $worker;

	public function __construct($host, $port, $queue, callable $factory = null)
	{
		parent::__construct($host, $port, $queue);

		$factory = $factory ?: function($queue) {
			return new Resque_Worker($queue);
		};

		$this->worker = $factory($this->queue);
		$this->worker->logLevel = Resque_Worker::LOG_VERBOSE;
	}

	public function consume()
	{
		$this->worker->work(5);
	}
}
