<?php

namespace atoum\telemetry\tests\units\controllers;

use mageekguy\atoum;
use Symfony\Component\HttpFoundation\JsonResponse;

class error extends atoum\test
{
	public function test__invoke()
	{
		$this
			->given(
				$code = rand(400, 599),
				$message = uniqid(),
				$exception = new \mock\Symfony\Component\HttpKernel\Exception\HttpException($code, $message)
			)
			->if($this->newTestedInstance)
			->then
				->object($response = $this->testedInstance->__invoke($exception, $code))->isInstanceOf(JsonResponse::class)
				->integer($response->getStatusCode())->isEqualTo($code)
				->string($response->getContent())->isEqualTo(json_encode([
					'status' => $code,
					'message' => $message
				]))
		;
	}
}
