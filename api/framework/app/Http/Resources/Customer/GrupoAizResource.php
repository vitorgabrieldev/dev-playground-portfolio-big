<?php

namespace App\Http\Resources\Customer;

class GrupoAizResource extends Resource
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
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array
	 */
	public function toArrayComplement($request)
	{
		return [
            'uuid',
			'text',
			$this->mergeWhen($this->resource->hasAttribute('file'), function () use ($request) {
				return [
					'file'       => $this->file ? asset($this->file) : null,
					'file_sizes' => $this->getFileSizes(),
				];
			}),	
            'type',
		];
	}
}
