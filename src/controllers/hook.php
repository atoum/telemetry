<?php

namespace atoum\telemetry\controllers;

use atoum\telemetry\resque\broker;
use atoum\telemetry\resque\jobs\release;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class hook
{
	/**
	 * @var string
	 */
	private $token;

	/**
	 * @var broker
	 */
	private $broker;

	public function __construct($token, broker $broker)
	{
		$this->token = $token;
		$this->broker = $broker;
	}

	/**
	 * @throws \InfluxDB\Exception
	 *
	 * @return Response
	 */
	public function __invoke($token, Request $request) : Response
	{
		$report = json_decode($request->getContent(false), true);

		if ($token !== $this->token)
		{
			throw new AccessDeniedHttpException();
		}

		if (isset($report['release']) === false)
		{
			throw new BadRequestHttpException();
		}

		return new JsonResponse($this->broker->enqueue(release::class, ['release' => $report['release']]));
	}
}
