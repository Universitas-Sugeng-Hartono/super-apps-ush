<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Daftar alias middleware kustom kamu
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'superadmin' => \App\Http\Middleware\IsSuperAdmin::class,
            'masteradmin' => \App\Http\Middleware\IsMasterAdmin::class,
            'student' => \App\Http\Middleware\EnsureStudentAuthenticated::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'api-key' => \App\Http\Middleware\ApiKeyMiddleware::class,
        ]);

       
        Authenticate::redirectUsing(function (Request $request) {
            return route('auth.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();