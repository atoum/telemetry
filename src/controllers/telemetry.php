<?php

namespace atoum\telemetry\controllers;

use atoum\telemetry\resque\broker;
use atoum\telemetry\resque\jobs\report;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
 *     @SWG\Property(name="memory", type="integer", description="Memory (in bytes)")
 * )
 *
 * @SWG\Model(
 *     id="Report",
 *     required="php, atoum, os, arch, vendor, project, metrics",
 *     @SWG\Property(name="php", type="string", description="PHP version number (x.y.z)"),
 *     @SWG\Property(name="atoum", type="string", description="atoum version number (x.y.z)"),
 *     @SWG\Property(name="os", type="string", description="Operating system description"),
 *     @SWG\Property(name="arch", type="string", description="Architecture"),
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

	public function __construct(broker $broker)
	{
		$this->broker = $broker;
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
	 * @throws BadRequestHttpException
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
