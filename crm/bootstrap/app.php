<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IpWhitelist;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\EnforceTwoFactor;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ── Global web middleware ────────────────────────────────────────
        $middleware->web(append: [
            IpWhitelist::class,
        ]);

        // ── Named middleware aliases ─────────────────────────────────────
        $middleware->alias([
            'role'              => RoleMiddleware::class,
            'enforce.2fa'       => EnforceTwoFactor::class,
            'auth.staff'        => \Illuminate\Auth\Middleware\Authenticate::class,
        ]);

        // ── Redirect unauthenticated users ───────────────────────────────
        $middleware->redirectGuestsTo(fn () => route('login'));

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
