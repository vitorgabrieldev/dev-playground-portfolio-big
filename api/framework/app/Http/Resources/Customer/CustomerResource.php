<?php

namespace App\Http\Resources\Customer;

class CustomerResource extends Resource
{

	/**
	 * Show relationships if they were loaded
	 *
	 * @var array
	 */
	protected $showRelations = [
        'preferencias' => PreferenciasResource::class,
		'perfil' => PerfisAcessoResource::class,
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
			'email',
            'phone',
			$this->mergeWhen($this->resource->hasAttribute('avatar'), function () use ($request) {
				return [
					'avatar'       => $this->avatar ? (is_url($this->avatar) ? $this->avatar : asset($this->avatar)) : null,
					'avatar_sizes' => $this->avatar ? (is_url($this->avatar) ? null : $this->getAvatarSizes()) : null,
				];
			}),
			'document',
			'cnpj',
            'network_external',
			'birth_date' => $this->birth_date ? $this->birth_date->format('Y-m-d') : null,
			'accept_newsletter',
			'email_verified_at',
			'account_verified_at',
            'accepted_term_of_users_at',
            'accepted_policy_privacy_at',
			'notify_general',
		];
	}
}
