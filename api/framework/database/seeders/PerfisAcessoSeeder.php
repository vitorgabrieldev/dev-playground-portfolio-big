<?php

namespace Database\Seeders;

use App\Models\PerfisAcesso;
use Illuminate\Database\Seeder;

class PerfisAcessoSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		PerfisAcesso::factory()->count(4)->create();
	}
}
