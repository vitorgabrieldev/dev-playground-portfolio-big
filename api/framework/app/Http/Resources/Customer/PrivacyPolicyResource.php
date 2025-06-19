<?php

namespace App\Http\Resources\Customer;

class PrivacyPolicyResource extends Resource
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
            'text'
		];
	}
}
