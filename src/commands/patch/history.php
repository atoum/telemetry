<?php

namespace atoum\telemetry\commands\patch;

use InfluxDB\Database;
use Knp\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class history extends Command
{
	/**
	 * @var Database
	 */
	private $database;

	public function __construct(Database $database)
	{
		parent::__construct('patch:history');

		$this->database = $database;
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$result = $this->database->query('SELECT * FROM _version');
		$points = $result->getPoints();

		$table = new Table($output);
		$table->setHeaders(['Date', 'Version']);

		foreach ($points as $point) {
			$table->addRow([
				date('Y-m-d H:i:s', strtotime($point['time'])),
				$point['value']
			]);
		}

		$table->render();

		return 0;
	}
}
