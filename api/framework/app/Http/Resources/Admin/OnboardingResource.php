<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class OnboardingResource extends JsonResource
{

	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return array
	 */
	public function toArray($request)
	{
		return [
			'uuid'        => $this->uuid,
			'frase'       => $this->frase,
			'frase2'      => $this->frase2,
			'type'       => $this->type,
			'created_at'  => $this->created_at->toAtomString(),
			'updated_at'  => $this->updated_at->toAtomString(),
			$this->mergeWhen($this->resource->hasAttribute('file'), function () use ($request) {
				return [
					'file'       => $this->file ? asset($this->file) : null,
					'file_sizes' => $this->getFileSizes(),
				];
			}),			
		];
	}
}
