<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) return null;

        // Employee portal routes → employee login
        if ($request->is('employee/*') || $request->is('employee')) {
            return route('employee.login');
        }

        // Admin portal routes → admin login
        return route('admin.login');
    }
}