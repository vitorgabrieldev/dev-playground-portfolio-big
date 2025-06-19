<?php

namespace Database\Factories;

use App\Models\HowItWorks;
use Illuminate\Database\Eloquent\Factories\Factory;

class HowItWorksFactory extends Factory
{

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = HowItWorks::class;

	/**
	 * Order sequential
	 *
	 * @var int
	 */
	static $order = 0;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition()
	{
		return [
			'uuid'      => uuid(),
			'name'      => $this->faker->sentence,
			'text'      => $this->faker->paragraphs($this->faker->numberBetween(1, 4), true),
			'order'     => static::$order++,
			'is_active' => $this->faker->boolean,
		];
	}
}
