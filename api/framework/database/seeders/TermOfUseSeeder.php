<?php

namespace Database\Seeders;

use App\Models\TermOfUse;
use Illuminate\Database\Seeder;

class TermOfUseSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		TermOfUse::factory()->count(1)->create();
	}
}
