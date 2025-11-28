<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule; // <-- IMPORTANTE

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Grupo API con Sanctum "stateful" (cookies/sesión)
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // (Opcional) otros ajustes de middleware...
        // $middleware->web(prepend: [], append: []);
        // $middleware->alias([...]);
    })
    // ⏱️ Programación de tareas
    ->withSchedule(function (Schedule $schedule) {
        // Ejecuta tu comando cada minuto
        $schedule->command('app:send-take-reminders')->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Personalización de excepciones (opcional)
        // $exceptions->report(function (Throwable $e) { ... });
        // $exceptions->render(function (Throwable $e, $request) { ... });
    })
    ->create();


