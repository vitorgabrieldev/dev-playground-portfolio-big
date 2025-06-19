<?php

namespace App\Http\Resources\Admin;

class VerMaisResource extends Resource
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
