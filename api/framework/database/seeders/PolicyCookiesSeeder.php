<?php

namespace Database\Seeders;

use App\Models\PolicyCookies;
use Illuminate\Database\Seeder;

class PolicyCookiesSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		PolicyCookies::factory()->create();
	}
}
