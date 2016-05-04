<?php

namespace atoum\telemetry\controllers;

use atoum\telemetry\exceptions\validation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;


class error
{
	/**
	 * @param HttpException $exception
	 * @param int           $code
	 *
	 * @return Response
	 */
	public function __invoke(HttpException $exception, $code) : Response
	{
		$content = [
			'status' => $code,
			'type' => get_class($exception),
			'message' => $exception->getMessage()
		];

		if ($exception instanceof validation)
		{
			$content['violations'] = [];

			foreach ($exception->getViolations() as $violation)
			{
				$content['violations'][$violation->getPropertyPath()] = $violation->getMessage();
			}
		}

		return new JsonResponse($content, $code);
	}
}
