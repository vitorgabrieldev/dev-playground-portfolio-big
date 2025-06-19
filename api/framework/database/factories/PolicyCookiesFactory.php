<?php

namespace Database\Factories;

use App\Models\PolicyCookies;
use Illuminate\Database\Eloquent\Factories\Factory;

class PolicyCookiesFactory extends Factory
{

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = PolicyCookies::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition()
	{
		return [
			'uuid' => uuid(),
			'name' => 'Política de cookies',
			'text' => '<h1>Política de cookies</h1><p>' . implode('</p><p>', $this->faker->paragraphs($this->faker->numberBetween(5, 10))) . '</p>',
		];
	}
}
