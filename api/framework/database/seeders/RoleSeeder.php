<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Role::create([
			'uuid'        => uuid(),
			'name'        => 'Super Administrador',
			'description' => 'Controle total, possui todas as permissões disponíveis.',
			'is_system'   => 1,
		])->permissions()->sync(Permission::pluck('id'));
	}
}
