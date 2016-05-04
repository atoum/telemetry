<?php

namespace atoum\telemetry\controllers;

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
		return new JsonResponse(['status' => $code, 'message' => $exception->getMessage()], $code);
	}
}
