<?php

namespace App\Http\Resources\Admin;

use App\Models\Admin;
use App\Models\User;

class SystemLogResource extends Resource
{

	/**
	 * Show relationships if they were loaded
	 *
	 * @var array
	 */
	protected $showRelations = [
		'user' => [
			User::class     => UserResource::class,
			Customer::class => CustomerResource::class,
		],
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
			'user_type',
			'user_id',
			'message',
			'level',
			'color' => $this->when($this->resource->hasAttribute('level'), function () {
				return $this->getColor();
			}),
			'ip',
			'user_agent',
			'method',
			'url',
			'context',
		];
	}
}
