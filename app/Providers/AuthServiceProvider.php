<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Bypass Super Admin — accès total à toutes les permissions
        Gate::before(function ($user, $ability) {
            if ($user && $user->hasRole('super_admin')) {
                return true;
            }

            return null;
        });
    }
}
