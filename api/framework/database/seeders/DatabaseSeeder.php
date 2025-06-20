<?php

namespace Database\Seeders;

use App\Models\PerfisAcesso;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

	/**
	 * Seed the application's database.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->call([
			// Settings
			SettingSeeder::class,

			// ACL
			PermissionSeeder::class,
			RoleSeeder::class,
			UserSeeder::class,

			// Customer
			CustomerSeeder::class,
			CustomerPaymentMethodSeeder::class,
			CustomerPayoutMethodSeeder::class,

			// Cities
			StateSeeder::class,
			CitySeeder::class,

			// Course Platform
			CategoriesSeeder::class,
			CourseSeeder::class,
			LessonSeeder::class,
			FaqSeeder::class,
		]);

		if( !is_environment_prod() )
		{
			$this->call([
				PerfisAcessoSeeder::class,
			]);
		}
	}
}
