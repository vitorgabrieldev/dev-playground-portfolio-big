<?php

namespace Database\Seeders;

use App\Models\AboutCompany;
use Illuminate\Database\Seeder;

class AboutCompanySeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		AboutCompany::factory()->create();
	}
}
