<?php

/**
 * Phase 19 — bootstrap/app.php patch
 *
 * Add SecureHeaders to the global web middleware stack.
 * Replace the existing $middleware->web(append: [...]) block with:
 */

use App\Http\Middleware\IpWhitelist;
use App\Http\Middleware\SecureHeaders;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\EnforceTwoFactor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->web(append: [
            IpWhitelist::class,
            SecureHeaders::class,   // ← Phase 19 addition
        ]);

        $middleware->alias([
            'role'        => RoleMiddleware::class,
            'enforce.2fa' => EnforceTwoFactor::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // In production, render a clean error page for common HTTP errors
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if (app()->isProduction() && in_array($e->getStatusCode(), [403, 404, 500, 503])) {
                return response()->view("errors.{$e->getStatusCode()}", [], $e->getStatusCode());
            }
        });
    })
    ->create();
