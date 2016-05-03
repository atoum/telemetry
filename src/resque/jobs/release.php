<?php

namespace atoum\telemetry\resque\jobs;

use InfluxDB\Database;
use InfluxDB\Point;

class release
{
	public $args = [];

	protected $database;

	public function setUp()
	{
		$application = include __DIR__ . '/../../bootstrap.php';

		$this->setDatabase($application['database']);
	}

	public function setDatabase(Database $database)
	{
		$this->database = $database;
	}

	public function perform()
	{
		$release = $this->args['release'];
		$semver = explode('.', $release['tag_name']);
		$semver[1] = $semver[1] ?? 0;
		$semver[2] = $semver[2] ?? 0;

		$points = [
			new Point(
				'releases',
				1,
				[
					'atoum' => implode('.', $semver),
					'major' => $semver[0],
					'minor' => $semver[1] ?? 0,
					'patch' => $semver[2] ?? 0
				],
				[],
				strtotime($release['created_at'])
			)
		];

		$this->database->writePoints($points, Database::PRECISION_SECONDS);
	}
}
