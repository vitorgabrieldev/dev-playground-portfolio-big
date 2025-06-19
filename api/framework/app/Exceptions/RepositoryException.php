<?php

namespace App\Exceptions;

use RuntimeException;

class RepositoryException extends RuntimeException
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
	 * Options
	 *
	 * @var string
	 */
	public $options = [];

	/**
	 * Type of option
	 *
	 * @var string
	 */
	public $option;

	/**
	 * Create a new exception instance.
	 *
	 * @param       $type
	 * @param array $options
	 *
	 * @return RepositoryException
	 */
	public static function make($type, $options = [])
	{
		if( $type === 'orderBy' )
		{
			$instance           = new static(__('errors.repository.invalid_orderby', ['field' => $options['field'], 'fields' => $options['fields']]));
			$instance->type     = $type;
			$instance->code     = 422;
			$instance->httpCode = 422;
			$instance->options  = $options;

			return $instance;
		}

		if( $type === 'limit' )
		{
			$instance           = new static(__('errors.repository.invalid_limit', ['limit_max' => $options['limit_max']]));
			$instance->type     = $type;
			$instance->code     = 422;
			$instance->httpCode = 422;
			$instance->options  = $options;

			return $instance;
		}

		if( $type === 'edit_system' )
		{
			$instance           = new static(__('errors.repository.edit_system', ['uuid' => $options['uuid']]));
			$instance->type     = $type;
			$instance->code     = 403;
			$instance->httpCode = 403;
			$instance->options  = $options;

			return $instance;
		}

		if( $type === 'delete_system' )
		{
			$instance           = new static(__('errors.repository.delete_system', ['uuid' => $options['uuid']]));
			$instance->type     = $type;
			$instance->code     = 403;
			$instance->httpCode = 403;
			$instance->options  = $options;

			return $instance;
		}

		if( $type === 'delete_current_user' )
		{
			$instance           = new static(__('errors.repository.delete_current_user'));
			$instance->type     = $type;
			$instance->code     = 403;
			$instance->httpCode = 403;
			$instance->options  = $options;

			return $instance;
		}

		if( $type === 'update_current_user' )
		{
			$instance           = new static(__('errors.repository.update_current_user'));
			$instance->type     = $type;
			$instance->code     = 403;
			$instance->httpCode = 403;
			$instance->options  = $options;

			return $instance;
		}

		if( $type === 'delete_role_with_users' )
		{
			$instance           = new static(__('errors.repository.delete_role_with_users'));
			$instance->type     = $type;
			$instance->code     = 403;
			$instance->httpCode = 403;
			$instance->options  = $options;

			return $instance;
		}

		if( $type === 'user_is_not_the_owner' )
		{
			$instance           = new static(__('errors.repository.user_is_not_the_owner'));
			$instance->type     = $type;
			$instance->code     = 403;
			$instance->httpCode = 403;

			return $instance;
		}

		$instance = new static(__('errors.unknown_exception', ['type' => $type]));

		return $instance;
	}

}
