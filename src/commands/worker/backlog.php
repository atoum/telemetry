<?php

namespace atoum\telemetry\commands\worker;

use atoum\telemetry\resque\broker;
use Knp\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class backlog extends Command
{
	/**
	 * @var broker
	 */
	private $broker;

	public function __construct(broker $broker)
	{
		parent::__construct('worker:backlog');

		$this->broker = $broker;
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$result = $this->broker->redis()->llen('failed');

		$output->writeln('There are <info>' . $result . '</info> failed jobs in the backlog.');

		$confirmation = new ConfirmationQuestion('Are you sure you want to requeue them? ');
		$answer = $this->getHelper('question')->ask($input, $output, $confirmation);

		if ($answer === true) {
			$i = 0;

			while ($job = $this->broker->redis()->lpop('failed')) {
				$job = json_decode($job);

				$this->broker->enqueue($job->payload->class, (array) $job->payload->args[0]);

				$output->writeln('Enqueued job <info>#' . ++$i . '</info> (' . $job->failed_at . ')');
			}
		}

		return 0;
	}
}
