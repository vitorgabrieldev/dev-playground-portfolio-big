<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		// Ignore Sanctum migrations
		Sanctum::ignoreMigrations();
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		// Issue Index Lengths - https://laravel.com/docs/8.x/migrations#index-lengths-mysql-mariadb
		Schema::defaultStringLength(191);

		// Time language
		setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf8', 'ptb', 'portuguese', 'Portuguese_Brazilian');

		// Set model morph map
		$files = scandir(app_path('Models'));

		$models = [];

		foreach( $files as $file )
		{
			if( $file == '.' || $file == '..' ) continue;

			$models[] = str_replace('.php', '', $file);
		}

		$morphMap = [];

		foreach( $models as $model )
		{
			$morphMap[Str::snake($model)] = 'App\Models\\' . $model;
		}

		Relation::morphMap($morphMap);

		// Paginator
		Paginator::defaultView('vendor/pagination/default');

		Paginator::defaultSimpleView('vendor/pagination/simple-default');
	}
}
