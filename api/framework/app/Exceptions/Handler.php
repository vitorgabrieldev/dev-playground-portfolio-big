<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{

	/**
	 * A list of the exception types that are not reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		GeneralException::class,
		RepositoryException::class,
		UserPermissionException::class,
		UserTypeException::class,
		DecryptException::class,
	];

	/**
	 * A list of the inputs that are never flashed for validation exceptions.
	 *
	 * @var array
	 */
	protected $dontFlash = [
		'password',
		'password_confirmation',
		'password_new',
		'password_new_confirmation',
	];

	/**
	 * Register the exception handling callbacks for the application.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->reportable(function (Throwable $e) {
			//
		});
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Throwable               $exception
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \Throwable
	 */
	public function render($request, Throwable $exception)
	{
		if( $request->expectsJson() )
		{
			if( $exception instanceof NotFoundHttpException )
			{
				return response()->json([
					'message' => $exception->getMessage() ? $exception->getMessage() : __('errors.not_found_exception'),
					'code'    => 404,
					'type'    => basename(get_class($exception)),
				], 404);
			}

			if( $exception instanceof ModelNotFoundException )
			{
				return response()->json([
					'message' => __('errors.model_not_found_exception'),
					'code'    => 404,
					'type'    => basename(get_class($exception)),
				], 404);
			}

			if( $exception instanceof MethodNotAllowedHttpException )
			{
				return response()->json([
					'message' => __('errors.model_not_allowed'),
					'code'    => 405,
					'type'    => basename(get_class($exception)),
				], 405);
			}

			if( $exception instanceof ValidationException )
			{
				return response()->json([
					'message' => __('errors.validation_fields'),
					'errors'  => $exception->errors(),
					'code'    => 422,
					'type'    => basename(get_class($exception)),
				], 422);
			}

			if( $exception instanceof AccessDeniedHttpException )
			{
				return response()->json([
					'message' => __('errors.access_denied'),
					'code'    => 403,
					'type'    => basename(get_class($exception)),
				], 403);
			}

			if( $exception instanceof AuthorizationException )
			{
				return response()->json([
					'message' => __('errors.authorization_denied'),
					'code'    => 403,
					'type'    => basename(get_class($exception)),
				], 403);
			}

			if( $exception instanceof AuthenticationException )
			{
				return response()->json([
					'message' => __('errors.authentication_exception'),
					'code'    => 401,
					'type'    => basename(get_class($exception)),
				], 401);
			}

			if( $exception instanceof RelationNotFoundException )
			{
				return response()->json([
					'message' => __('errors.relation_not_found_exception', ['relation' => $exception->relation, 'model' => basename(get_class($exception->model))]),
					'code'    => 400,
					'type'    => basename(get_class($exception)),
				], 400);
			}

			if( $exception instanceof ThrottleRequestsException )
			{
				return response()->json([
					'message' => __('errors.throttle_requests_exception'),
					'code'    => 429,
					'type'    => basename(get_class($exception)),
				], 429);
			}

			if( $exception instanceof PostTooLargeException )
			{
				return response()->json([
					'message' => __('errors.post_too_large_exception'),
					'code'    => 413,
					'type'    => basename(get_class($exception)),
				], 413);
			}

			if( $exception instanceof UserTypeException )
			{
				return response()->json([
					'message' => $exception->getMessage(),
					'code'    => $exception->getCode(),
					'type'    => basename(get_class($exception)),
				], $exception->httpCode);
			}

			if( $exception instanceof UserPermissionException )
			{
				return response()->json([
					'message' => $exception->getMessage(),
					'code'    => $exception->getCode(),
					'type'    => basename(get_class($exception)),
				], $exception->httpCode);
			}

			if( $exception instanceof RepositoryException )
			{
				return response()->json([
					'message' => $exception->getMessage(),
					'code'    => $exception->getCode(),
					'type'    => basename(get_class($exception)),
				], $exception->httpCode);
			}

			if( $exception instanceof GeneralException )
			{
				return response()->json([
					'message' => $exception->getMessage(),
					'code'    => $exception->getCode(),
					'type'    => basename(get_class($exception)),
				], $exception->httpCode);
			}

			if( $exception instanceof Throwable )
			{
				return response()->json([
					'message' => $exception->getMessage() ? $exception->getMessage() : __('errors.general_exception'),
					'code'    => $exception->getCode(),
					'type'    => basename(get_class($exception)),
				], 500);
			}
		}

		return parent::render($request, $exception);
	}
}
