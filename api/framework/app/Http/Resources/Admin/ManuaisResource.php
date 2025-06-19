<?php

namespace App\Http\Resources\Admin;

class ManuaisResource extends Resource
{
	/**
	 * Show relationships if they were loaded
	 *
	 * @var array
	 */
	protected $showRelations = [
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
