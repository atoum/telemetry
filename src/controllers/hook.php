<?php

namespace atoum\telemetry\controllers;

use atoum\telemetry\resque\broker;
use atoum\telemetry\resque\jobs\release;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @SWG\Model(
 *     id="Release",
 *     required="tag_name, created_at",
 *     @SWG\Property(name="tag_name", type="string", description="Version number (x.y.z)"),
 *     @SWG\Property(name="created_at", type="string", description="Release date (ISO 8601)")
 * )
 *
 * @SWG\Model(
 *     id="Event",
 *     required="release",
 *     @SWG\Property(
 *         name="release",
 *         type="Release",
 *         description="Release informations"
 *     )
 * )
 */

/**
 * @SWG\Resource(basePath="/")
 */
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
	 * @SWG\Api(
	 *     path="/hook/{token}",
	 *     @SWG\Operation(
	 *         method="POST",
	 *         @SWG\Consumes("application/json"),
	 *         @SWG\Produces("application/json"),
	 *         @SWG\Parameter(
	 *             paramType="path",
	 *             type="string",
	 *             name="token",
	 *             required=true,
	 *             description="Authentication token"
	 *         ),
	 *         @SWG\Parameter(
	 *             paramType="body",
	 *             type="Event",
	 *             name="body",
	 *             required=true,
	 *             description="Event payload"
	 *         ),
	 *         @SWG\ResponseMessage(
	 *             code=200,
	 *             message="Event acknowledged"
	 *         ),
	 *         @SWG\ResponseMessage(
	 *             code=400,
	 *             message="Invalid event payload"
	 *         ),
	 *         @SWG\ResponseMessage(
	 *             code=403,
	 *             message="Access denied"
	 *         ),
	 *         @SWG\ResponseMessage(
	 *             code=404,
	 *             message="Route is not available"
	 *         )
	 *     )
	 * )
	 *
	 * @throws AccessDeniedHttpException
	 * @throws BadRequestHttpException
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
