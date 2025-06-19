<?php

namespace App\Http\Resources\Customer;

class CategoriasTreinamentoResource extends Resource
{
	/**
	 * Show relationships if they were loaded
	 *
	 * @var array
	 */
	protected $showRelations = [
		'treinamento' => TreinamentoResource::class,
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
			'uuid',
			'name',
			'order',
			'is_active',
		];
	}
}
