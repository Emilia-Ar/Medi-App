<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Solo en producción (Railway)
        if (app()->environment('production')) {
            // Forzar https en las URLs generadas por Laravel
            URL::forceScheme('https');

            // Ignorar los warnings de usuario (como el de WebPush sobre GMP/BCMath)
            // para que no se conviertan en ErrorException 500
            error_reporting(E_ALL & ~E_USER_WARNING & ~E_USER_DEPRECATED);
        }
    }
}
