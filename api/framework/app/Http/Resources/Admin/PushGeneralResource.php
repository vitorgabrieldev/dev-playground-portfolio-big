<?php

namespace App\Http\Resources\Admin;

class PushGeneralResource extends Resource
{

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
