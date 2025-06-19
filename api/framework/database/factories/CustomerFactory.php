<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\PerfisAcesso;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Customer::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition()
	{
		$avatar = $this->faker->boolean ? null : 'storage/_default/avatar-' . $this->faker->numberBetween(1, 10) . '.jpg';

		return [
			'uuid'							=> uuid(),
			'perfil_acesso_id'				=> PerfisAcesso::inRandomOrder()->first(['id'])->id,
			'name'							=> $this->faker->name,
			'document'						=> $this->faker->numerify('###.###.###-##'),
			'cnpj'							=> $this->faker->numerify('##.###.###/####-##'),
            'phone'							=> $this->faker->phoneNumber,
			'email'							=> $this->faker->unique()->safeEmail,
			'avatar'						=> $avatar,
			'email_verified_at'				=> now(),
			'account_verified_at'			=> now(),
			'birth_date'					=> $this->faker->date('Y-m-d', 'now'),
            'accepted_term_of_users_at'		=> now(),
            'accepted_policy_privacy_at'	=> now(),
			'password'						=> bcrypt('123456'),
			'remember_token'				=> Str::random(10),
			'is_active'						=> $this->faker->boolean,
			'accept_newsletter'				=> $this->faker->boolean,
		];
	}
}
