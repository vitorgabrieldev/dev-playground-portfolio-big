<?php

namespace Database\Factories;

use App\Models\AboutApp;
use Illuminate\Database\Eloquent\Factories\Factory;

class AboutAppFactory extends Factory
{

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = AboutApp::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition()
	{
		return [
			'uuid' => uuid(),
			'name' => 'Sobre',
			'text' => '<p>' . implode('</p><p>', $this->faker->paragraphs($this->faker->numberBetween(5, 10))) . '</p>',
		];
	}
}
