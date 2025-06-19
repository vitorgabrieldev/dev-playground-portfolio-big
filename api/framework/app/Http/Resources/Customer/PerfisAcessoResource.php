<?php

namespace App\Http\Resources\Customer;

class PerfisAcessoResource extends Resource
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
			'name',
			'order',
			'is_active',
		];
	}
}
