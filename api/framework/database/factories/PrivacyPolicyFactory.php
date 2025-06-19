<?php

namespace Database\Factories;

use App\Models\PrivacyPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrivacyPolicyFactory extends Factory
{

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = PrivacyPolicy::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition()
	{
		return [
			'uuid' => uuid(),
			'name' => 'Política de privacidade',
			'text' => '<h1>Política de privacidade</h1><p>' . implode('</p><p>', $this->faker->paragraphs($this->faker->numberBetween(5, 10))) . '</p>',
		];
	}
}
