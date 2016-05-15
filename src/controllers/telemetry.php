<?php

namespace atoum\telemetry\controllers;

use atoum\telemetry\exceptions;
use atoum\telemetry\resque\broker;
use atoum\telemetry\resque\jobs\report;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @SWG\Model(
 *     id="Assertions",
 *     required="classes, methods, assertions, exceptions, errors, duration, memory",
 *     @SWG\Property(name="total", type="integer", description="Number of assertions"),
 *     @SWG\Property(name="passed", type="integer", description="Number of passed assertions"),
 *     @SWG\Property(name="failed", type="integer", description="Number of failed assertions")
 * )
 *
 * @SWG\Model(
 *     id="Methods",
 *     required="total, void, uncomplete, skipped, failed, errored, exception",
 *     @SWG\Property(name="total", type="integer", description="Number of test methods"),
 *     @SWG\Property(name="void", type="integer", description="Number of void methods"),
 *     @SWG\Property(name="uncomplete", type="integer", description="Number of uncomplete methods"),
 *     @SWG\Property(name="skipped", type="integer", description="Number of skipped methods"),
 *     @SWG\Property(name="failed", type="integer", description="Number of failed methods"),
 *     @SWG\Property(name="errored", type="integer", description="Number of errored methods"),
 *     @SWG\Property(name="exception", type="integer", description="Number of methods with exceptions")
 * )
 * @SWG\Model(
 *     id="Coverage",
 *     @SWG\Property(name="lines", type="float", description="Lines coverage %"),
 *     @SWG\Property(name="branches", type="float", description="Branches coverage %"),
 *     @SWG\Property(name="paths", type="float", description="Paths coverage %")
 * )
 *
 * @SWG\Model(
 *     id="Metrics",
 *     required="classes, methods, assertions, exceptions, errors, duration, memory",
 *     @SWG\Property(name="classes", type="integer", description="Number of test classes"),
 *     @SWG\Property(name="methods", type="Methods", description="Methods metrics"),
 *     @SWG\Property(name="assertions", type="Assertions", description="Assertions metrics"),
 *     @SWG\Property(name="exceptions", type="integer", description="Number of exceptions"),
 *     @SWG\Property(name="errors", type="integer", description="Number of erorrs"),
 *     @SWG\Property(name="duration", type="integer", description="Duration (seconds)"),
 *     @SWG\Property(name="memory", type="integer", description="Memory (in bytes)"),
 *     @SWG\Property(name="coverage", type="Coverage", description="Code coverage")
 * )
 *
 * @SWG\Model(
 *     id="Report",
 *     required="php, atoum, os, arch, vendor, project, metrics",
 *     @SWG\Property(name="php", type="string", description="PHP version number (x.y.z)"),
 *     @SWG\Property(name="atoum", type="string", description="atoum version number (x.y.z)"),
 *     @SWG\Property(name="os", type="string", description="Operating system description"),
 *     @SWG\Property(name="arch", type="string", description="Architecture"),
 *     @SWG\Property(name="environment", type="string", description="CI environment"),
 *     @SWG\Property(name="vendor", type="string", description="Vendor name"),
 *     @SWG\Property(name="project", type="string", description="Project name"),
 *     @SWG\Property(name="metrics", type="Metrics", description="Metrics")
 * )
 */

/**
 * @SWG\Resource(basePath="/")
 */
class telemetry
{
	/**
	 * @var broker
	 */
	private $broker;

	/**
	 * @var ValidatorInterface
	 */
	private $validator;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	public function __construct(broker $broker, ValidatorInterface $validator, LoggerInterface $logger)
	{
		$this->broker = $broker;
		$this->validator = $validator;
		$this->logger = $logger;
	}

	/**
	 * @SWG\Api(
	 *     path="/",
	 *     @SWG\Operation(
	 *         method="POST",
	 *         @SWG\Consumes("application/json"),
	 *         @SWG\Produces("application/json"),
	 *         @SWG\Parameter(
	 *             paramType="body",
	 *             type="Report",
	 *             name="body",
	 *             required=true,
	 *             description="Report payload"
	 *         ),
	 *         @SWG\ResponseMessage(
	 *             code=200,
	 *             message="Report acknowledged"
	 *         ),
	 *         @SWG\ResponseMessage(
	 *             code=400,
	 *             message="Invalid report payload"
	 *         )
	 *     )
	 * )
	 *
	 * @param Request $request
	 *
	 * @throws BadRequestHttpException
	 * @throws exceptions\validation
	 *
	 * @return Response
	 */
	public function __invoke(Request $request) : Response
	{
		$report = json_decode($request->getContent(false), true);

		if (null === $report)
		{
			throw new BadRequestHttpException('Invalid report payload');
		}

		$integer = [
			'constraints' => [
				new Constraints\NotNull(),
				new Constraints\Type('integer'),
				new Constraints\GreaterThanOrEqual(0)
			]
		];

		$numeric = [
			'constraints' => [
				new Constraints\NotNull(),
				new Constraints\Type('numeric'),
				new Constraints\GreaterThanOrEqual(0)
			]
		];

		$constraint = new Constraints\Collection([
			'php' => new Constraints\Required([new Constraints\NotBlank(), new Constraints\Regex('/\d+(?:\.\d+){0,2}/')]),
			'atoum' => new Constraints\Required([new Constraints\NotBlank(), new Constraints\Regex('/^(?:\d+(?:\.(?:\d+|[xX]$)){0,2}|dev-.*?)$/')]),
			'os' => new Constraints\NotBlank(),
			'arch' => new Constraints\NotBlank(),
			'environment' => new Constraints\Optional(['constraints' => [new Constraints\NotBlank()]]),
			'vendor' => new Constraints\Required([new Constraints\NotBlank(), new Constraints\Regex('/^[a-z0-9_.-]+$/')]),
			'project' => new Constraints\Required([new Constraints\NotBlank(), new Constraints\Regex('/^[a-z0-9_.-]+$/')]),
			'metrics' => new Constraints\Collection([
				'classes' => new Constraints\Required($integer),
				'exceptions' => new Constraints\Required($integer),
				'errors' => new Constraints\Required($integer),
				'duration' => new Constraints\Required([
					'constraints' => [
						new Constraints\NotNull(),
						new Constraints\Type('numeric'),
						new Constraints\GreaterThanOrEqual(0)
					]
				]),
				'memory' => new Constraints\Required($integer),
				'methods' => new Constraints\Collection([
					'total' => new Constraints\Required($integer),
					'void' => new Constraints\Required($integer),
					'uncomplete' => new Constraints\Required($integer),
					'skipped' => new Constraints\Required($integer),
					'failed' => new Constraints\Required($integer),
					'errored' => new Constraints\Required($integer),
					'exception' => new Constraints\Required($integer),
				]),
				'assertions' => new Constraints\Collection([
					'total' => new Constraints\Required($integer),
					'passed' => new Constraints\Required($integer),
					'failed' => new Constraints\Required($integer),
				]),
				'coverage' => new Constraints\Optional([
					'constraints' => [
						new Constraints\Collection([
							'lines' => new Constraints\Optional($numeric),
							'branches' => new Constraints\Optional($numeric),
							'paths' => new Constraints\Optional($numeric)
						])
					]
				])
			])
		]);

		$errors = $this->validator->validate($report, $constraint);

		if ($errors->count() > 0)
		{
			foreach ($errors as $error) {
				$this->logger->warning(
					$error->getPropertyPath() . ' ' . $error->getMessage(),
					['actual' => $error->getInvalidValue(), 'atoum' => isset($report['atoum']) ? $report['atoum'] : null]
				);
			}

			throw new exceptions\validation($errors);
		}

		return new JsonResponse($this->broker->enqueue(report::class, ['report' => $report, 'timestamp' => time()]));
	}
}
