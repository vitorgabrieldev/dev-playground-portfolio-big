<?php

namespace Database\Factories;

use App\Models\AboutCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

class AboutCompanyFactory extends Factory
{

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = AboutCompany::class;

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
