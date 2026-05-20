<?php

// Bootstrap de la aplicación (sin BOM).
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('api', \App\Http\Middleware\Cors::class);
        $middleware->appendToGroup('api', \App\Http\Middleware\SyncHttpStatusCode::class);
        $middleware->alias([
            'jwt' => \App\Http\Middleware\JwtAuth::class,
            'tipo' => \App\Http\Middleware\RequireTipo::class,
            'web.user' => \App\Http\Middleware\EnsureWebUserAuthenticated::class,
            'web.admin' => \App\Http\Middleware\EnsureWebAdminAuthenticated::class,
        ]);
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
