<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{

	/**
	 * The policy mappings for the application.
	 *
	 * @var array
	 */
	protected $policies = [// 'App\Models\Model' => 'App\Policies\ModelPolicy',
	];

	/**
	 * Register any authentication / authorization services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerPolicies();

		// // só acessaria o dashboard do websocket quem tem permissao
		// Gate::define('viewWebSocketsDashboard', function ($user = null) {
		// 	return user_has_permission(['websockets.dashboard']);
		// });

        // Adicione o login via rede sociais
        Auth::provider('authsocialnetwork', function($app, array $config) {
            return new AuthSocialNetworkUserProvider($app['hash'], $config['model']);
        });
	}
}
