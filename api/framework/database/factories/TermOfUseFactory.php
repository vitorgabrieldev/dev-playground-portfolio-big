<?php

namespace Database\Factories;

use App\Models\TermOfUse;
use Illuminate\Database\Eloquent\Factories\Factory;

class TermOfUseFactory extends Factory
{

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = TermOfUse::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition()
	{
		return [
			'uuid' => uuid(),
			'name' => 'Termos de uso',
			'text' => '<h1>Termos de uso</h1><p>' . implode('</p><p>', $this->faker->paragraphs($this->faker->numberBetween(5, 10))) . '</p>',
		];
	}
}
