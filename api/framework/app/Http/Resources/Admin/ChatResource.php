<?php

namespace App\Http\Resources\Admin;

class ChatResource extends Resource
{

	/**
	 * Show relationships if they were loaded
	 *
	 * @var array
	 */
	protected $showRelations = [
        'user' => [
            Customer::class => CustomerResource::class,
            Professional::class => ProfissionalResource::class,
        ],
        'pedidoSocorro' => PedidoSocorroResource::class,
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
            'message',
            'lido',
			'author_uuid' => $this->whenLoaded('user', function () {
				return $this->user->uuid;
			}),
			'author_name' => $this->whenLoaded('user', function () {
				return $this->user->name;
			}),
			'type' => $this->whenLoaded('user', function () {
				return class_basename(get_class($this->user));
			}),
		];
	}
}
