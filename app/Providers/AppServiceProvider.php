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
        if (app()->environment('production')) {
            // 🔒 Forzar HTTPS en todas las URLs generadas
            URL::forceScheme('https');

            // 🛡 Ignorar SOLO el warning de GMP/BCMath que dispara WebPush
            $previousHandler = set_error_handler(function ($severity, $message, $file, $line) use (&$previousHandler) {
                // Si el mensaje es el famoso de GMP/BCMath, lo tragamos y NO dejamos que Laravel lo convierta en excepción
                if (str_contains($message, 'It is highly recommended to install the GMP or BCMath extension')) {
                    return true; // Stop: no pasa al handler de Laravel
                }

                // Para todo lo demás, delegamos al handler anterior (el de Laravel/Symfony)
                if ($previousHandler) {
                    return $previousHandler($severity, $message, $file, $line);
                }

                // Dejar que PHP haga lo que haría normalmente
                return false;
            });
        }
    }
}

