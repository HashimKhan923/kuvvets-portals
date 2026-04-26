<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminPortalMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Must be logged in
        if (!Auth::check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Please login to access the admin portal.');
        }

        $user = Auth::user();

        // Must be active
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('admin.login')
                ->with('error', 'Your account has been deactivated. Contact your administrator.');
        }

        // Must be admin type
        if (!$user->isAdmin()) {
            Auth::logout();
            return redirect()->route('admin.login')
                ->with('error', 'You do not have permission to access the admin portal.');
        }

        // Must have admin portal access
        if (!$user->canAccessAdminPortal()) {
            Auth::logout();
            return redirect()->route('admin.login')
                ->with('error', 'Admin portal access is not enabled for your account.');
        }

        // Account locked?
        if ($user->isLocked()) {
            Auth::logout();
            return redirect()->route('admin.login')
                ->with('error', 'Account locked until ' . $user->locked_until->format('h:i A') . '. Contact HR.');
        }

        return $next($request);
    }
}