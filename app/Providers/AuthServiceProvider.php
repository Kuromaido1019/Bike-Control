<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Models\Bike;
use App\Policies\BikePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Bike::class => BikePolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        // Cambia a verificación estricta de strings
        Gate::define('isAdmin', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('isGuard', function ($user) {
            return $user->role === 'guardia';
        });

        Gate::define('isVisitor', function ($user) {
            return $user->role === 'visitante';
        });
    }
}
