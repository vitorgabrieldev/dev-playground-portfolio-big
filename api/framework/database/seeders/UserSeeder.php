<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		User::factory()->create([
			'name'      => 'vitorgabrieldev',
			'email'     => 'vitorgabrieldeoliveiradev@gmail.com',
			'avatar'    => null,
			'is_active' => 1,
		])->roles()->sync([1]);

		if( is_environment_local() )
		{
			User::factory()->count(5)->create()->each(function ($item) {
				/** @var User $item */
				$item->roles()->sync(Role::pluck('id'));
			});
		}
	}
}
