<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserActive
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $redirectRoute = ($request->is('employee/*') || $request->is('employee'))
                ? 'employee.login'
                : 'admin.login';

            return redirect()->route($redirectRoute)
                ->with('error', 'Your account has been deactivated. Contact your administrator.');
        }

        return $next($request);
    }
}