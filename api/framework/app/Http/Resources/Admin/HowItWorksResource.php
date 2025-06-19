<?php

namespace App\Http\Resources\Admin;

class HowItWorksResource extends Resource
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
