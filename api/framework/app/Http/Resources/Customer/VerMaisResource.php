<?php

namespace App\Http\Resources\Customer;

class VerMaisResource extends Resource
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
			'text',
			$this->mergeWhen($this->resource->hasAttribute('icone'), function () use ($request) {
				return [
					'icone'       => $this->icone ? asset($this->icone) : null,
					'icone_sizes' => $this->getIconeSizes(),
				];
			}),
			'video',
			'order',
			$this->mergeWhen($this->resource->hasAttribute('capa'), function () use ($request) {
				return [
					'capa'       => $this->capa ? asset($this->capa) : null,
					'capa_sizes' => $this->getCapaSizes(),
				];
			}),
			'is_active',
		];
	}
}
