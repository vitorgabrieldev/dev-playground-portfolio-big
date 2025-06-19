<?php

namespace Database\Seeders;

use App\Models\HowItWorks;
use Illuminate\Database\Seeder;

class HowItWorksSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		HowItWorks::factory()->count(10)->create();
	}
}
