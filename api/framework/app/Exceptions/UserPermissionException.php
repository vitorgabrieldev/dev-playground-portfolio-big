<?php

namespace App\Exceptions;

use RuntimeException;

class UserPermissionException extends RuntimeException
{

	/**
	 * Error code
	 *
	 * @var integer
	 */
	public $code = 403;

	/**
	 * Permission
	 *
	 * @var string
	 */
	public $permissions;

	/**
	 * Http status
	 *
	 * @var integer
	 */
	public $httpCode = 403;

	/**
	 * Create a new exception instance.
	 *
	 * @param $permissions
	 *
	 * @return UserPermissionException
	 */
	public static function make($permissions)
	{
		$instance = new static(__('errors.user_permission_exception', ['permissions' => implode(' or ', $permissions)]));

		$instance->permissions = $permissions;

		return $instance;
	}

}
