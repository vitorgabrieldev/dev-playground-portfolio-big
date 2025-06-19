<?php

namespace App\Http\Resources\Admin;

class NewsResource extends Resource
{

	/**
	 * Show relationships if they were loaded
	 *
	 * @var array
	 */
	protected $showRelations = [
        'media' => MediaResource::class,
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
			'name',
			'text',
			'sinopse',
			'data_inicio',
			'data_expiracao',
			'is_active'
		];
	}
}
