<?php

namespace App\Http\Controllers\v1\Customer;

use App\Exceptions\RepositoryException;
use App\Models\ProfissionalActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class Controller extends BaseController
{

	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	protected $fieldSortable = [];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [];

	/**
	 * Default ordering
	 *
	 * @var array
	 */
	protected $orderByDefault = [
		[
			'name' => 'id',
			'sort' => 'asc',
		],
	];

	/**
	 * Limit results
	 *
	 * @var integer
	 */
	protected $limit = 20;

	/**
	 * Max limit results
	 *
	 * @var integer
	 */
	protected $limitMax = 100;

	/**
	 * Get logged user
	 *
	 * @return mixed
	 */
	protected function user()
	{
		return current_user_customer();
	}

	protected function log($action, $item_name, $message, $item = null, $item_old = null)
	{
		$request = request();

		$qs = $request->getQueryString();

		// Get current user
		$user = $this->user();

		$data = [
			'uuid'          => uuid(),
			'activity_name' => Str::limit($item_name, 191),
			'message'       => Str::limit($message, 191),
			'action'        => $action,
			'old_data'      => $item_old,
			'new_data'      => $item,
			'ip'            => $request->ip(),
			'user_agent'    => Str::ascii($request->userAgent()),
			'url'           => urldecode($request->getPathInfo() . ($qs !== null ? '?' . $qs : '')),
			'method'        => $request->getMethod(),
		];

		$log = new ProfissionalActivity($data);

		if( $item instanceof Model )
		{
			$log->activity()->associate($item);
		}

		$user->activity_logs()->save($log);
	}

	/**
	 * Prepares the fields for sorting
	 *
	 * @param null $orderBy
	 *
	 * @return array|null
	 */
	protected function getOrderBy($orderBy = null)
	{
		if( empty($orderBy) )
		{
			return $this->orderByDefault;
		}

		$return = [];

		// Get fields
		$orderByFields = explode('|', $orderBy);

		if( $orderByFields )
		{
			foreach( $orderByFields as $orderByField )
			{
				// Get column and direction
				$fieldExp  = explode(':', $orderByField);
				$fieldName = $fieldExp[0];

				// Validate field
				if( !in_array($fieldName, $this->fieldSortable) )
				{
					throw RepositoryException::make('orderBy', ['field' => $fieldName, 'fields' => "'" . implode("', '", $this->fieldSortable) . "'"]);
				}

				$return[] = [
					'name' => $fieldName,
					'sort' => strtolower($fieldExp[1] ?? 'asc') == 'asc' ? 'asc' : 'desc',
				];
			}
		}

		return $return;
	}

	/**
	 * Check if exist orderBy
	 *
	 * @param array $orderByList
	 * @param mixed ...$search
	 *
	 * @return bool
	 */
	protected function hasOrderBy(array $orderByList, ...$search)
	{
		foreach( $orderByList as $orderBy )
		{
			if( in_array($orderBy['name'], $search) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Prepares limit results
	 *
	 * @param integer $limit
	 *
	 * @return int|null
	 */
	protected function getLimit($limit = null)
	{
		$limit = (int) $limit;

		if( empty($limit) )
		{
			return $this->limit;
		}

		if( $limit > $this->limitMax )
		{
			throw RepositoryException::make('limit', ['limit_max' => $this->limitMax]);
		}

		return $limit;
	}
}
