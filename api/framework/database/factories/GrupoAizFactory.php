<?php

namespace Database\Factories;

use App\Models\GrupoAiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrupoAizFactory extends Factory
{

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = GrupoAiz::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition()
	{
		return [
			'uuid'      => uuid(),
			'text'      => $this->faker->sentence,
		];
	}
}
