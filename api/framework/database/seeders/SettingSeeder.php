<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;

class SettingSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Setting::truncate();

		$items = [];

		$items['general']['app_url_ios']                    = 'https://apps.apple.com/us/app/facebook/id284882215';
		$items['general']['app_url_android']                = 'https://play.google.com/store/apps/details?id=com.facebook.katana';
		$items['general']['wpp_geral']                      = '5511999999999';
		$items['general']['wpp_equip']                      = '5511999999999';
        $items['general']['catalogo'] 		                = "";
        $items['general']['email_support']					= "contato@grupoaiz.com.br";

		$items['notifications']['delete_account']           = 'contato@grupoaiz.com.br';
		$items['notifications']['create_account']           = 'contato@grupoaiz.com.br';

		foreach( Arr::dot($items) as $key => $val )
		{
			Setting::create([
				'key' => $key,
				'val' => $val,
			]);
		}

		// Cache new config
		Artisan::call('config:cache');
	}
}
