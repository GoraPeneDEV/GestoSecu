<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            if (file_exists(base_path('routes/portail.php'))) {
                Route::middleware('web')
                    ->prefix('portail')
                    ->group(base_path('routes/portail.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'portail.auth' => \App\Http\Middleware\PortailAuthMiddleware::class,
            'portail.filter' => \App\Http\Middleware\PortailDataFilterMiddleware::class,
            'portail.menu' => \App\Http\Middleware\PortailMenuMiddleware::class,
            'guest.portail' => \App\Http\Middleware\RedirectIfAuthenticatedPortail::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\LoadMenuMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
