<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeePortalMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('employee.login')
                ->with('error', 'Please sign in to access the employee portal.');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('employee.login')
                ->with('error', 'Your account has been deactivated.');
        }

        if ($user->isLocked()) {
            Auth::logout();
            return redirect()->route('employee.login')
                ->with('error', 'Account is temporarily locked. Contact HR.');
        }

        if (!$user->canAccessEmployeePortal()) {
            Auth::logout();
            return redirect()->route('employee.login')
                ->with('error', 'Employee portal access is not enabled for this account.');
        }

        if (!$user->employee) {
            Auth::logout();
            return redirect()->route('employee.login')
                ->with('error', 'No employee profile linked to this account. Contact HR.');
        }

        return $next($request);
    }
}