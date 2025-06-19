<?php

namespace App\Exceptions;

use RuntimeException;

class GeneralException extends RuntimeException
{

	/**
	 * Error code
	 *
	 * @var integer
	 */
	public $code = 400;

	/**
	 * Type
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Http status
	 *
	 * @var integer
	 */
	public $httpCode = 400;

	/**
	 * Create a new exception instance.
	 *
	 * @param $type
	 *
	 * @return static
	 */
	public static function make($type)
	{
		if( $type === 'invalid_login' )
		{
			$instance           = new static(__('auth.failed'));
			$instance->type     = $type;
			$instance->code     = 403;
			$instance->httpCode = 403;

			return $instance;
		}
		if( $type === 'not_verified' )
		{
			$instance           = new static(__('auth.not_verified'));
			$instance->type     = $type;
			$instance->code     = 403;
			$instance->httpCode = 403;

			return $instance;
		}

		if( $type === 'push_cant_delete_past_date' )
		{
			$instance           = new static(__('errors.general.push_cant_delete_past_date'));
			$instance->type     = $type;
			$instance->code     = 400;
			$instance->httpCode = 400;

			return $instance;
		}

		if( $type === 'required_search_field' )
		{
			$instance           = new static(__('errors.general.required_search_field'));
			$instance->type     = $type;
			$instance->code     = 400;
			$instance->httpCode = 400;

			return $instance;
		}

		if( $type === 'zipcode_not_found' )
		{
			$instance           = new static(__('errors.general.zipcode_not_found'));
			$instance->type     = $type;
			$instance->httpCode = 404;

			return $instance;
		}

		return new static(__('errors.unknown_exception', ['type' => $type]));
	}

	/**
	 * @param     $message
	 * @param int $httpCode
	 * @param int $code
	 *
	 * @return static
	 */
	public static function free($message, int $httpCode = 400, int $code = 400)
	{
		$instance           = new static($message);
		$instance->type     = 'general';
		$instance->code     = $code;
		$instance->httpCode = $httpCode;

		return $instance;
	}
}
