<?php

namespace Database\Seeders;

use App\Models\Onboarding;
use Illuminate\Database\Seeder;

class OnboardingSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Onboarding::factory()->count(1)->create();
	}
}
