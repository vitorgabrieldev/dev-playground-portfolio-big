<?php

namespace App\Http\Resources\Customer;

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
        // Obtém o usuário autenticado
        $user = auth()->user() ?? null;
        
        // Obtém os valores do pivot (relacionamento) se existir
        $read = null;
        $deleted = null;
        
        if ($user) {
			$read = (bool) ($user->notifications()->where('notifications.id',$this->resource->id)->first() ? $user->notifications()->where('notifications.id',$this->resource->id)->first()->pivot->read ?? false : false);
			$deleted = (bool) ($user->notifications()->where('notifications.id',$this->resource->id)->first() ? $user->notifications()->where('notifications.id',$this->resource->id)->first()->pivot->deleted ?? false : false);
        }

		return [
			'name',
			'text',
			'descricao',
			'video',
			'titulo_button',
			'link_button',
			'data_envio',
			'send_push',
            'read' => $read,
            'deleted' => $deleted,
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
