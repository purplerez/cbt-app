<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //Tambahan Middleware untuk spatie
        $middleware->alias([
            'role'                => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'          => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission'  => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'ensure.kepala.session' => \App\Http\Middleware\EnsureKepalaSession::class,
        ]);

        // DISABLED: Append EnsureApiToken to the default web middleware group
        // This middleware creates Sanctum tokens on every request, causing CPU spikes on shared hosting
        // If you need API tokens, only use them for dedicated API routes, not web routes
        // $middleware->appendToGroup('web', \App\Http\Middleware\EnsureApiToken::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
