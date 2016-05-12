<?php

namespace atoum\telemetry\resque\jobs;

use InfluxDB\Database;
use InfluxDB\Point;

class report
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
		$report = $this->args['report'];

		preg_match('/(?:PHP )?(\d+\.\d+\.\d+)/', $report['php'], $php);

		$tags = [
			'php' => $php[1],
			'atoum' => $report['atoum'],
			'os' =>  $report['os'],
			'arch' =>  $report['arch'],
			'vendor' => $report['vendor'],
			'project' => $report['project']
		];

		if (isset($reports['environment']) === true)
		{
			$tags['environment'] = $reports['environment'];
		}

		$points = [
			new Point(
				'suites',
				1,
				$tags,
				[
					'classes' => $report['metrics']['classes'],
					'methods' => $report['metrics']['methods']['total'],
					'assertions' => $report['metrics']['assertions']['total'],
					'exceptions' => $report['metrics']['exceptions'],
					'errors' => $report['metrics']['errors'],
					'memory' => $report['metrics']['memory'],
					'duration' => $report['metrics']['duration'],
				],
				time()
			),
			new Point(
				'assertions',
				$report['metrics']['assertions']['total'],
				$tags,
				[
					'passed' => $report['metrics']['assertions']['passed'],
					'failed' => $report['metrics']['assertions']['failed']
				],
				time()
			),
			new Point(
				'methods',
				$report['metrics']['methods']['total'],
				$tags,
				[
					'void' => $report['metrics']['methods']['void'],
					'uncomplete' => $report['metrics']['methods']['uncomplete'],
					'skipped' => $report['metrics']['methods']['skipped'],
					'failed' => $report['metrics']['methods']['failed'],
					'errored' => $report['metrics']['methods']['errored'],
					'exception' => $report['metrics']['methods']['exception'],
				],
				time()
			)
		];

		if (isset($report['metrics']['coverage']) === true && isset($report['metrics']['coverage']['lines']) === true)
		{
			$values = [];

			if (isset($report['metrics']['coverage']['branches']) === true)
			{
				$values['branches'] = $report['metrics']['coverage']['branches'];
			}

			if (isset($report['metrics']['coverage']['paths']) === true)
			{
				$values['paths'] = $report['metrics']['coverage']['paths'];
			}

			$points[] = new Point('coverage', $report['metrics']['coverage']['lines'], $tags, $values, time());
		}

		$this->database->writePoints($points, Database::PRECISION_SECONDS);
	}
}
