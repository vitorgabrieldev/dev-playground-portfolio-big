<?php

namespace Database\Seeders;

use App\Models\CustomerReportProblem;
use Illuminate\Database\Seeder;

class CustomerReportProblemSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		CustomerReportProblem::factory()->count(30)->create();
	}
}
