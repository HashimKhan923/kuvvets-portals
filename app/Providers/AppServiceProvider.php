<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── Super-admin bypass ──────────────────────────────────────
        // If the user has the "super_admin" role, grant every permission.
        Gate::before(function ($user, $ability) {
            if ($user && method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
                return true;
            }
            return null; // let normal checks continue
        });
    }
}