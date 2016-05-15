<?php

namespace atoum\telemetry\tests\units\resque\jobs;

use InfluxDB\Database;
use InfluxDB\Point;
use mageekguy\atoum;
use atoum\telemetry;

class report extends atoum\test
{
	public function test__construct()
	{
		$this
			->given($this->newTestedInstance)
			->then
				->array($this->testedInstance->args)->isEmpty
		;
	}

	public function testPerform()
	{
		$this
			->given(
				$this->mockGenerator->orphanize('__construct'),
				$database = new \mock\InfluxDB\Database(),
				$this->calling($database)->writePoints->doesNothing,
				$report = [
					'php' => 'PHP ' . phpversion() . '-' . uniqid(),
					'atoum' => atoum\version,
					'os' => php_uname('s') . ' ' . php_uname('r'),
					'arch' => php_uname('m'),
					'vendor' => 'atoum',
					'project' => 'telemetry',
					'metrics' => [
						'classes' => 1,
						'methods' => [
							'total' => 2,
							'void' => 0,
							'uncomplete' => 0,
							'skipped' => 1,
							'failed' => 0,
							'errored' => 0,
							'exception' => 0,
						],
						'assertions' => [
							'total' => 42,
							'passed' => 42,
							'failed' => 0
						],
						'exceptions' => 0,
						'errors' => 0,
						'duration' => 5,
						'memory' => memory_get_usage(),
					]
				]
			)
			->if(
				$this->newTestedInstance,
				$this->testedInstance->setDatabase($database),
				$this->testedInstance->args['report'] = $report
			)
			->when($this->testedInstance->perform())
			->then
				->mock($database)
					->call('writePoints')->withArguments(
						[
							new Point(
								'suites',
								1,
								[
									'php' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION,
									'atoum' => $report['atoum'],
									'os' =>  $report['os'],
									'arch' =>  $report['arch'],
									'environment' =>  'unknown',
									'vendor' => $report['vendor'],
									'project' => $report['project']
								],
								[
									'classes' => $report['metrics']['classes'],
									'methods' => $report['metrics']['methods']['total'],
									'assertions' => $report['metrics']['assertions']['total'],
									'exceptions' => $report['metrics']['exceptions'],
									'errors' => $report['metrics']['errors'],
									'memory' => $report['metrics']['memory'],
									'duration' => floatval(sprintf('%.14f', $report['metrics']['duration'])),
								],
								time()
							),
							new Point(
								'assertions',
								$report['metrics']['assertions']['total'],
								[
									'php' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION,
									'atoum' => $report['atoum'],
									'os' =>  $report['os'],
									'arch' =>  $report['arch'],
									'environment' =>  'unknown',
									'vendor' => $report['vendor'],
									'project' => $report['project']
								],
								[
									'passed' => $report['metrics']['assertions']['passed'],
									'failed' => $report['metrics']['assertions']['failed']
								],
								time()
							),
							new Point(
								'methods',
								$report['metrics']['methods']['total'],
								[
									'php' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION,
									'atoum' => $report['atoum'],
									'os' =>  $report['os'],
									'arch' =>  $report['arch'],
									'environment' =>  'unknown',
									'vendor' => $report['vendor'],
									'project' => $report['project']
								],
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
						],
						Database::PRECISION_SECONDS
					)->once
		;
	}
}
