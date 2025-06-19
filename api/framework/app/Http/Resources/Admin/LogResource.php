<?php

namespace App\Http\Resources\Admin;

class LogResource extends Resource
{

	/**
	 * Show relationships if they were loaded
	 *
	 * @var array
	 */
	protected $showRelations = [
		'user' => UserResource::class,
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
			'user_id',
			'log_type',
			'log_id',
			'log_name',
			'message',
			'action',
			'old_data',
			'new_data',
			'ip',
			'user_agent',
			'method',
			'url',
		];
	}
}
