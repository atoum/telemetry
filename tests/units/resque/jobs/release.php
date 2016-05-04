<?php

namespace atoum\telemetry\tests\units\resque\jobs;

use InfluxDB\Database;
use InfluxDB\Point;
use mageekguy\atoum;
use atoum\telemetry;

class release extends atoum\test
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
				$release = [
					'tag_name' => '1.0.0',
					'created_at' => date('c')
				]
			)
			->if(
				$this->newTestedInstance,
				$this->testedInstance->setDatabase($database),
				$this->testedInstance->args['release'] = $release
			)
			->when($this->testedInstance->perform())
			->then
				->mock($database)
					->call('writePoints')->withArguments(
						[
							new Point(
								'releases',
								1,
								[
									'atoum' => $release['tag_name'],
									'major' => $release['tag_name'][0],
									'minor' => $release['tag_name'][2],
									'patch' => $release['tag_name'][4]
								],
								[],
								strtotime($release['created_at'])
							)
						],
						Database::PRECISION_SECONDS
					)->once
			->given($release = [
				'tag_name' => '2',
				'created_at' => date('c')
			])
			->if($this->testedInstance->args['release'] = $release)
			->when($this->testedInstance->perform())
			->then
				->mock($database)
					->call('writePoints')->withArguments(
						[
							new Point(
								'releases',
								1,
								[
									'atoum' => $release['tag_name'] . '.0.0',
									'major' => $release['tag_name'][0],
									'minor' => '0',
									'patch' => '0',
								],
								[],
								strtotime($release['created_at'])
							)
						],
						Database::PRECISION_SECONDS
					)->once
			->given($release = [
				'tag_name' => '2.1',
				'created_at' => date('c')
			])
			->if($this->testedInstance->args['release'] = $release)
			->when($this->testedInstance->perform())
			->then
				->mock($database)
					->call('writePoints')->withArguments(
						[
							new Point(
								'releases',
								1,
								[
									'atoum' => $release['tag_name'] . '.0',
									'major' => $release['tag_name'][0],
									'minor' => $release['tag_name'][2],
									'patch' => '0',
								],
								[],
								strtotime($release['created_at'])
							)
						],
						Database::PRECISION_SECONDS
					)->once
		;
	}
}
