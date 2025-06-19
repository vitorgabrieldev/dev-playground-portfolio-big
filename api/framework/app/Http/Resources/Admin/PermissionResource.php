<?php

namespace App\Http\Resources\Admin;

class PermissionResource extends Resource
{

	/**
	 * Show relationships if they were loaded
	 *
	 * @var array
	 */
	protected $showRelations = [
		'roles' => RoleResource::class,
	];

	/**
	 * Complement to transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array
	 */
	public function toArrayComplement($request)
	{
		return [
			'key',
			'group',
		];
	}
}
