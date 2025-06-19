<?php

namespace App\Http\Resources\Customer;

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
		// Obtém o usuário autenticado
		$user = auth()->user() ?? null;

		// Obtém os valores do pivot (relacionamento) se existir
		$read = null;
		$favorited = null;

		if ($user) {
			$read = (bool) ($user->treinamentos()->where('treinamento.id',$this->resource->id)->first() ? $user->treinamentos()->where('treinamento.id',$this->resource->id)->first()->pivot?->read ?? false : false);
			$favorited = (bool) ($user->treinamentos()->where('treinamento.id',$this->resource->id)->first() ? $user->treinamentos()->where('treinamento.id',$this->resource->id)->first()->pivot?->favorited ?? false : false);
		}

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
            'read' => $read,
            'favorited' => $favorited,
		];
	}
}
