<?php

namespace atoum\telemetry\exceptions;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class validation extends BadRequestHttpException
{
	/**
	 * @var ConstraintViolationListInterface
	 */
	private $violations;

	public function __construct(ConstraintViolationListInterface $violations, \Exception $previous = null, $code = Response::HTTP_BAD_REQUEST)
	{
		parent::__construct('Validation failed', $previous, $code);

		$this->violations = $violations;
	}

	public function getViolations() : ConstraintViolationListInterface
	{
		return $this->violations;
	}
}
