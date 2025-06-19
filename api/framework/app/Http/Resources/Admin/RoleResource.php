<?php

namespace App\Http\Resources\Admin;

class RoleResource extends Resource
{

	/**
	 * Show relationships if they were loaded
	 *
	 * @var array
	 */
	protected $showRelations = [
		'users'       => UserResource::class,
		'permissions' => PermissionResource::class,
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
			'description',
			'permissions_count' => $this->when(!is_null($this->permissions_count), $this->permissions_count),
			'users_count'       => $this->when(!is_null($this->users_count), $this->users_count),
		];
	}
}
