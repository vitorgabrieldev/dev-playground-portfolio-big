<?php

namespace Database\Seeders;

use App\Models\Banners;
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

			// Other
			AboutCompanySeeder::class,
			AboutAppSeeder::class,
			PrivacyPolicySeeder::class,
			PolicyCookiesSeeder::class,
			TermOfUseSeeder::class,
			OnboardingSeeder::class,
			GrupoAizSeeder::class,
		]);

		if( !is_environment_prod() )
		{
			$this->call([
				PerfisAcessoSeeder::class,
				CustomerSeeder::class,
				FaqSeeder::class,
				BannersSeeder::class,
				HowItWorksSeeder::class,
				CustomerReportProblemSeeder::class,
				NewsSeeder::class,
				//
			]);
		}
	}
}
