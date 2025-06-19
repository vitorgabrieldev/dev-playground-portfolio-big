<?php

namespace App\Exceptions;

use RuntimeException;

class UserTypeException extends RuntimeException
{

	/**
	 * Error code
	 *
	 * @var integer
	 */
	public $code = 403;

	/**
	 * Type of user
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Http status
	 *
	 * @var integer
	 */
	public $httpCode = 403;

	/**
	 * Create a new exception instance.
	 *
	 * @param $type
	 *
	 * @return UserTypeException
	 */
	public static function make($type)
	{
		$instance = new static(__('errors.user_type_exception', ['type' => $type]));

		$instance->type = $type;

		return $instance;
	}

}
