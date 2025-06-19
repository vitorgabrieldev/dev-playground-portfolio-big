<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerReportProblem;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerReportProblemFactory extends Factory
{

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = CustomerReportProblem::class;

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
		$is_answered = $this->faker->boolean;

		return [
			'uuid'        => uuid(),
			'customer_id' => Customer::inRandomOrder()->first(['id'])->id,
			'message'     => $this->faker->realText($this->faker->numberBetween(10, 500)),
			'answer'      => $is_answered ? $this->faker->paragraphs($this->faker->numberBetween(2, 10), true) : null,
			'is_answered' => $is_answered,
			'answered_at' => $is_answered ? $this->faker->dateTimeBetween('-60 days', 'now') : null,
		];
	}
}
