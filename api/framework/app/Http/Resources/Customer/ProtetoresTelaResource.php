<?php

namespace App\Http\Resources\Customer;

class ProtetoresTelaResource extends Resource
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
			$this->mergeWhen($this->resource->hasAttribute('file'), function () use ($request) {
				return [
					'file'       => $this->file ? asset($this->file) : null,
				];
			}),
			$this->mergeWhen($this->resource->hasAttribute('capa'), function () use ($request) {
				return [
					'capa'       => $this->capa ? asset($this->capa) : null,
					'capa_sizes' => $this->getCapaSizes(),
				];
			}),	
			'order',
			'is_active',
		];
	}
}
