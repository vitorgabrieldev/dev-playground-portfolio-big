<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class AuthSocialNetworkUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials)) {
            return null;
        }

        $query = $this->createModel()->newQuery();

        // Adicione as verificações dos campos
        if (isset($credentials['login_external_id'])) {
            $query->where('login_external_id', $credentials['login_external_id']);
        }

        if (isset($credentials['network_external'])) {
            $query->where('network_external', $credentials['network_external']);
        }

        if (isset($credentials['email'])) {
            $query->where('email', $credentials['email']);
        }

        if (isset($credentials['is_active'])) {
            $query->where('is_active', $credentials['is_active']);
        }

        return $query->first();
    }

    public function validateCredentials(UserContract $user, array $credentials)
    {
        // Sempre retorna true, ignorando a senha
        return true;
    }
}