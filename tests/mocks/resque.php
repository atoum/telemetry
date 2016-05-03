<?php

final class resque
{
	private static $dsn;
	private static $enqueued;

	public static function setBackend($dsn)
	{
		self::$dsn = $dsn;
	}

	public static function getBackend()
	{
		return self::$dsn;
	}

	public static function enqueue($queue, $class, $args = null, $trackStatus = false)
	{
		self::$enqueued = func_get_args();

		return uniqid();
	}

	public static function getLastEnqueued()
	{
		return self::$enqueued;
	}
}
