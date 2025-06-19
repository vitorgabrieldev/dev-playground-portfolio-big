<?php

namespace Database\Factories;

use App\Models\PerfisAcesso;
use Illuminate\Database\Eloquent\Factories\Factory;

class PerfisAcessoFactory extends Factory
{

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = PerfisAcesso::class;

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
			'name'      => $this->faker->name,
			'order'     => static::$order++,
		];
	}
}
