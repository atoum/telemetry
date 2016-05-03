<?php

namespace atoum\telemetry;


abstract class resque
{
	protected $queue;

	public function __construct($host, $port, $queue)
	{
		$dsn = sprintf('%s:%d', $host, $port);

		\Resque::setBackend($dsn);

		$this->queue = $queue;
	}
}
