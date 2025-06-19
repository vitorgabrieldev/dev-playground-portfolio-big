<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Customer::factory()->create([
			'name'      => 'Customer teste',
			'email'     => 'usuario@teste.com',
			'is_active' => 1,
		]);

		if( is_environment_local() )
		{
			Customer::factory()->count(49)->create();
		}
	}
}
