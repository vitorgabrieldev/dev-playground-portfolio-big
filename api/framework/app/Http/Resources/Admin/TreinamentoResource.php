<?php

namespace App\Http\Resources\Admin;

class TreinamentoResource extends Resource
{
	/**
	 * Show relationships if they were loaded
	 *
	 * @var array
	 */
	protected $showRelations = [
		'categoria' => CategoriasTreinamentoResource::class,
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
			$this->mergeWhen($this->resource->hasAttribute('capa'), function () use ($request) {
				return [
					'capa'       => $this->capa ? (is_url($this->capa) ? $this->capa : asset($this->capa)) : null,
					'capa_sizes' => $this->capa ? (is_url($this->capa) ? null : $this->getCapaSizes()) : null,
				];
			}),
			'video',
			'duracao',
			'descricao',
			'destaque',
			'order',
			'is_active',
		];
	}
}
