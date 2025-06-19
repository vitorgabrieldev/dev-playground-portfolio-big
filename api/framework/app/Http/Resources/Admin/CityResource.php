<?php

namespace App\Http\Resources\Admin;

class CityResource extends Resource
{

	/**
	 * Show relationships if they were loaded
	 *
	 * @var array
	 */
	protected $showRelations = [
		'state' => StateResource::class,
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
			'full_name' => $this->whenLoaded('state', function () {
				return $this->name . '-' . $this->state->abbr;
			}),
			'ibge',
			'lat',
			'lon',
		];
	}
}
