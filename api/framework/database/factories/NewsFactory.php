<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\Stores;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = News::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition()
	{
		return [
			'uuid'      => uuid(),
			'name'     => $this->faker->name,
			'sinopse'     => $this->faker->name,
			'text'      => $this->faker->paragraphs($this->faker->numberBetween(1, 4), true),
			'data_inicio' => $this->faker->dateTime(),
			'is_active' => $this->faker->boolean,
		];
	}
}
