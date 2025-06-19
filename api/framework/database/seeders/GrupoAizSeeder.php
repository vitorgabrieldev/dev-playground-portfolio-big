<?php

namespace Database\Seeders;

use App\Models\GrupoAiz;
use Illuminate\Database\Seeder;

class GrupoAizSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		GrupoAiz::factory()->create();
	}
}
