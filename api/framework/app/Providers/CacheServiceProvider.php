<?php

namespace App\Providers;

use App\Extensions\CacheFileStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->booting(function () {
			Cache::extend('file', function ($app, $config) {
				return Cache::repository(new CacheFileStore($app['files'], $config['path'], $config['permission'] ?? null));
			});
		});
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}
}