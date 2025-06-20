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

			// Cities
			StateSeeder::class,
			CitySeeder::class,

			// Course Platform
			CategorySeeder::class,
			CourseSeeder::class,
			LessonSeeder::class,
			FaqSeeder::class,

			// Customer
			CustomerSeeder::class,
			CustomerPaymentMethodSeeder::class,
			CustomerPayoutMethodSeeder::class,

			// Producer Balance
			ProducerBalanceSeeder::class,
		]);

		if( !is_environment_prod() )
		{
			$this->call([
				PerfisAcessoSeeder::class,
			]);
		}
	}
}
