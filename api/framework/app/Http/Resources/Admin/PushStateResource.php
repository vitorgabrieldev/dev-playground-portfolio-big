<?php

namespace App\Http\Resources\Admin;

class PushStateResource extends Resource
{

	/**
	 * Show relationships if they were loaded
	 *
	 * @var array
	 */
	protected $showRelations = [
		'state' => StateResource::class,
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
			'title',
			'body',
			'url',
			'total_users',
			'api_response',
			'scheduled_at',
			'send_at',
		];
	}
}
