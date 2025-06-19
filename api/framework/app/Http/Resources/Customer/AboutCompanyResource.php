<?php

namespace App\Http\Resources\Customer;

class AboutCompanyResource extends Resource
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
			'name',
			'file',
			'file_mobile',
			'video',
		];
	}
}
