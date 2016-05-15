<?php

namespace atoum\telemetry\commands\patch;

use InfluxDB\Database;
use InfluxDB\Point;
use Knp\Command\Command;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class apply extends Command
{
	/**
	 * @var Database
	 */
	private $database;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	public function __construct(Database $database, LoggerInterface $logger)
	{
		parent::__construct('patch:apply');

		$this->database = $database;
		$this->logger = $logger;
	}

	protected function configure()
	{
		parent::configure();

		$this->addOption('force', 'f', InputOption::VALUE_NONE, 'Apply patches');
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$result = $this->database->query('SELECT * FROM _version');
		$points = $result->getPoints();

		if ($input->getOption('force') === false)
		{
			$messages = [
				'Dry-Mode',
				'',
				'You are running in dry-mode. Use the --force option to apply patches.'
			];
			$output->writeln(['', $this->getHelper('formatter')->formatBlock($messages, 'bg=yellow', true), '']);
		}

		if (count($points) === 0)
		{
			$lastVersion = $this->commit(0, new NullOutput(), $input->getOption('force'));
		}
		else
		{
			$lastVersion = array_pop($points)['value'];
		}

		$output->writeln('Current database version is <info>' . $lastVersion . '</info>');

		$this->patch($lastVersion, $output, $input->getOption('force'));

		return 0;
	}

	private function patch($from, OutputInterface $output, $force = false)
	{
		if ($from < 1) {
			$output->writeln('Applying patch <info>1</info>');

			$this->apply(
				'suites',
				function($point) {
					return new Point(
						'suites',
						$point['value'],
						[
							'php' => $point['php'],
							'atoum' => $point['atoum'],
							'os' =>  $point['os'] ?: 'unknown',
							'arch' =>  $point['arch'] ?: 'unknown',
							'environment' =>  $point['environment'] ?: 'unknown',
							'vendor' => $point['vendor'],
							'project' => $point['project']
						],
						[
							'classes' => $point['classes'],
							'methods' => $point['methods'],
							'assertions' => $point['assertions'],
							'exceptions' => $point['exceptions'],
							'errors' => $point['errors'],
							'memory' => $point['memory'],
							'duration' => floatval(sprintf('%.14f', $point['duration'])),
						],
						strtotime($point['time'])
					);
				},
				$output,
				$force
			);

			$this->apply(
				'coverage',
				function($point) {
					return new Point(
						'coverage',
						$point['value'],
						[
							'php' => $point['php'],
							'atoum' => $point['atoum'],
							'os' =>  $point['os'] ?: 'unknown',
							'arch' =>  $point['arch'] ?: 'unknown',
							'environment' =>  $point['environment'] ?: 'unknown',
							'vendor' => $point['vendor'],
							'project' => $point['project']
						],
						[
							'lines' => floatval(sprintf('%.14f', $point['lines'])),
							'branches' => floatval(sprintf('%.14f', $point['branches'])),
							'paths' => floatval(sprintf('%.14f', $point['paths'])),
						],
						strtotime($point['time'])
					);
				},
				$output,
				$force
			);

			$this->commit(1, $output, $force);
		}
	}

	private function apply($measurement, callable $map, OutputInterface $output, $force = false)
	{
		$output->writeln('  > Patching the <info>' . $measurement . '</info> measurement');

		$result = $this->database->query('SHOW MEASUREMENTS');
		$measurements = array_map(function($point) { return $point['name']; }, $result->getPoints());

		if (in_array($measurement, $measurements) === false)
		{
			$output->writeln('<comment>    > Measurement ' . $measurement . ' does not exist</comment>');

			return;
		}

		$result = $this->database->query('SELECT * FROM ' . $measurement);
		$points = array_map($map, $result->getPoints());

		if ($force === true)
		{
			$this->database->query('DROP MEASUREMENT ' . $measurement);
			$this->database->writePoints($points, Database::PRECISION_SECONDS);
		}

		$output->writeln('    > Patched <info>' . count($points) . '</info> data points');
	}

	private function commit($version, OutputInterface $output, $force = false)
	{
		$output->writeln('Applied patch <info>' . $version . '</info>');
		list($usec, $sec) = explode(' ', microtime());
		$timestamp = sprintf('%d%06d', $sec, $usec * 1000000);

		if ($force === true)
		{
			$point = new Point('_version', $version, [], [], $timestamp);
			$this->database->writePoints([$point], Database::PRECISION_MICROSECONDS);
		}

		return $version;
	}
}
