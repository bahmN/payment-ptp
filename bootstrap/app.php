<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            '/payments/gateway/init',
            '/payments/gateway/antilopay/callback',
            '/payments/gateway/alikassa/callback',
            '/payments/gateway/digisellerCallback',
            '/bot/webhook/options',

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
