<?php

namespace App\Http\Resources\Admin;

class TermOfUseResource extends Resource
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
			'text',
		];
	}
}
