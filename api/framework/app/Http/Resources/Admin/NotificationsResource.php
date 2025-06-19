<?php

namespace App\Http\Resources\Admin;


class NotificationsResource extends Resource
{

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
			'name',
			'text',
			'descricao',
			'video',
			'titulo_button',
			'link_button',
			'data_envio',
			'send_push',
			'is_active',
			$this->mergeWhen($this->resource->hasAttribute('capa'), function () use ($request) {
				return [
					'capa'       => $this->capa ? asset($this->capa) : null,
					'capa_sizes' => $this->getCapaSizes(),
				];
			}),			
		];
	}
}
