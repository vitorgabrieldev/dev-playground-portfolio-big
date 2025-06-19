<?php

namespace App\Http\Resources\Admin;

class UserResource extends Resource
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
	 * @param $request
	 *
	 * @return array
	 */
	public function toArrayComplement($request)
	{
		return [
			'email',
			$this->mergeWhen($this->resource->hasAttribute('avatar'), function () use ($request) {
				return [
					'avatar'       => $this->avatar ? asset($this->avatar) : null,
					'avatar_sizes' => $this->getAvatarSizes(),
				];
			}),
			'custom_data',
			'permissions' => $this->whenLoaded('roles', function () {
				return $this->myPermissions();
			}),
		];
	}
}
