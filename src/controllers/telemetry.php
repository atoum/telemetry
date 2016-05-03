<?php

namespace atoum\telemetry\controllers;

use atoum\telemetry\resque\broker;
use atoum\telemetry\resque\jobs\report;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class telemetry
{
	/**
	 * @var broker
	 */
	private $broker;

	public function __construct(broker $broker)
	{
		$this->broker = $broker;
	}

	/**
	 * @throws \InfluxDB\Exception
	 *
	 * @return Response
	 */
	public function __invoke(Request $request) : Response
	{
		$report = json_decode($request->getContent(false), true);

		if (is_array($report) === false)
		{
			throw new BadRequestHttpException();
		}

		return new JsonResponse($this->broker->enqueue(report::class, ['report' => $report]));
	}
}
