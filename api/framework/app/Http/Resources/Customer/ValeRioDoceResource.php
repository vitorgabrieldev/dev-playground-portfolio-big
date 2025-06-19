<?php

namespace App\Http\Resources\Customer;

class ValeRioDoceResource extends Resource
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
			$this->mergeWhen($this->resource->hasAttribute('file'), function () use ($request) {
				return [
					'file'       => $this->file ? asset($this->file) : null,
				];
			}),
			'descricao',
			'order',
			'is_active',
		];
	}
}
