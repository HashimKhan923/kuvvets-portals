<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ── Use our custom Authenticate middleware ────────────
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('employee/*') || $request->is('employee')) {
                return route('employee.login');
            }
            return route('admin.login');
        });

        // ── Web middleware stack ──────────────────────────────
        $middleware->web(append: [
            \App\Http\Middleware\CheckUserActive::class,
            \App\Http\Middleware\TrackLastActivity::class,
        ]);

        // ── Aliases ───────────────────────────────────────────
        $middleware->alias([
            'admin.portal'       => \App\Http\Middleware\AdminPortalMiddleware::class,
            'employee.portal' => \App\Http\Middleware\EmployeePortalMiddleware::class,
            'guest.redirect'     => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();