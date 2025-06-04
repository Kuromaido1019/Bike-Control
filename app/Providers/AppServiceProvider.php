<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar zona horaria para todo PHP y Carbon
        date_default_timezone_set(config('app.timezone', 'America/Santiago'));
        \Carbon\Carbon::setLocale(config('app.locale', 'es'));
    }
}
