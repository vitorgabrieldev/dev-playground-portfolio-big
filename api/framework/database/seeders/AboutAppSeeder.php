<?php

namespace Database\Seeders;

use App\Models\AboutApp;
use Illuminate\Database\Seeder;

class AboutAppSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		AboutApp::factory()->create();
	}
}
