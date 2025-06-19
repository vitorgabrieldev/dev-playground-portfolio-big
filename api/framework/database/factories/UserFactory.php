<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = User::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition()
	{
		#$avatar = $this->faker->boolean ? null : 'storage/avatar/' . faker_image(public_path('storage/avatar'), 170, 170);
		$avatar = $this->faker->boolean ? null : 'storage/_default/avatar-' . $this->faker->numberBetween(1, 10) . '.jpg';

		return [
			'uuid'              => uuid(),
			'name'              => $this->faker->name,
			'email'             => $this->faker->unique()->safeEmail,
			'avatar'            => $avatar,
			'email_verified_at' => now(),
			'password'          => bcrypt('123456'),
			'remember_token'    => Str::random(10),
			'is_active'         => $this->faker->boolean,
		];
	}
}
