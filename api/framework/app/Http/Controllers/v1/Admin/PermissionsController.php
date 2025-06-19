<?php

namespace App\Http\Controllers\v1\Admin;

use App\Models\Permission;
use Illuminate\Http\Request;

/**
 * Class PermissionsController
 *
 * @resource Permissions
 * @package  App\Http\Controllers\v1\Admin
 */
class PermissionsController extends Controller
{

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
		'key',
		'name',
		'group',
		'order',
		'created_at',
		'updated_at',
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
		'key',
		'name',
		'group',
	];

	/**
	 * Autocomplete permissions
	 *
	 * @param Request $request
	 *
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `key` `name` `group` `order` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `key` `name` `group`
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function autocomplete(Request $request)
	{
		$this->validate($request, [
			'orderBy' => 'sometimes|nullable|string',
			'search'  => 'sometimes|nullable|string',
		]);

		$this->fieldSortable = [
			'id',
			'key',
			'name',
			'group',
			'order',
			'created_at',
			'updated_at',
		];

		$this->fieldSearchable = [
			'uuid',
			'key',
			'name',
			'group',
		];

		$items = Permission::query();

		$search  = $request->input('search');
		$orderBy = $this->getOrderBy($request->input('orderBy'));

		if( $search and count($this->fieldSearchable) )
		{
			$items->where(function ($query) use ($search) {
				foreach( $this->fieldSearchable as $searchField )
				{
					$query->orWhere($searchField, 'like', '%' . $search . '%');
				}
			});
		}

		foreach( $orderBy as $orderByField )
		{
			$items->orderBy($orderByField['name'], $orderByField['sort']);
		}

		$items = $items->get();

		$data = [];

		foreach( $items as $item )
		{
			$keyEx = explode('.', $item['key']);

			if( isset($data[$keyEx[0]]['permissions']) )
			{
				$data[$keyEx[0]]['permissions'][] = [
					'uuid'  => $item['uuid'],
					'key'   => $item['key'],
					'name'  => $item['name'],
					'group' => $item['group'],
					'order' => $item['order'],
				];

				continue;
			}

			$data[$keyEx[0]] = [
				'name'        => $item['group'],
				'key'         => $keyEx[0],
				'permissions' => [
					[
						'uuid'  => $item['uuid'],
						'key'   => $item['key'],
						'name'  => $item['name'],
						'group' => $item['group'],
						'order' => $item['order'],
					],
				],
			];
		}

		$data = array_values($data);

		return response()->json(['data' => $data]);
	}
}
