<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{

	/**
	 * The path to the "home" route for your application.
	 *
	 * This is used by Laravel authentication to redirect users after login.
	 *
	 * @var string
	 */
	public const HOME = '/home';

	/**
	 * The controller namespace for the application.
	 *
	 * When present, controller route declarations will automatically be prefixed with this namespace.
	 *
	 * @var string|null
	 */
	protected $namespace = 'App\\Http\\Controllers';

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->configureRateLimiting();

		// Patterns
		Route::pattern('id', '[0-9]+');
		Route::pattern('uuid', '[\da-fA-F]{8}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{12}');

		$this->routes(function () {
			// Admin routes
			Route::prefix('api/v1/admin')->middleware('api')->namespace($this->namespace . '\v1\Admin')->group(base_path('routes/apiAdmin.php'));

			// Customer routes
			Route::prefix('api/v1/customer')->middleware('api')->namespace($this->namespace . '\v1\Customer')->group(base_path('routes/apiCustomer.php'));

			// Web routes
			Route::middleware('web')->namespace($this->namespace . '\Web')->group(base_path('routes/web.php'));
		});
	}

	/**
	 * Configure the rate limiters for the application.
	 *
	 * @return void
	 */
	protected function configureRateLimiting()
	{
		RateLimiter::for('api', function (Request $request) {
			return Limit::perMinute(config('api.default_rate_limit'))->by(optional($request->user())->id ? : $request->ip());
		});
	}
}
