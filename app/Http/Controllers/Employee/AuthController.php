<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->canAccessEmployeePortal()) {
            return redirect()->route('employee.dashboard');
        }
        return view('employee.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $this->checkRateLimit($request);

        // Support email | username | employee_id login
        $identifier = trim($request->login);
        $user = null;

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $identifier)->first();
        } else {
            $user = User::where('username', $identifier)->first();
            if (!$user) {
                // Try employee_id lookup
                $emp = \App\Models\Employee::where('employee_id', $identifier)->first();
                if ($emp && $emp->user) $user = $emp->user;
            }
        }

        if ($user) {
            if (!$user->is_active) {
                throw ValidationException::withMessages([
                    'login' => 'Your account is deactivated. Contact HR.',
                ]);
            }
            if ($user->isLocked()) {
                throw ValidationException::withMessages([
                    'login' => 'Account locked until '
                        . $user->locked_until->format('h:i A') . '. Try again later.',
                ]);
            }
            if (!$user->canAccessEmployeePortal()) {
                throw ValidationException::withMessages([
                    'login' => 'This account does not have employee portal access.',
                ]);
            }
        }

        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $loginVal = $user && !filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? $user->username : $identifier;

        if (!Auth::attempt(
            [$field => $loginVal, 'password' => $request->password],
            $request->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey($request), 300);

            if ($user) {
                $attempts = $user->failed_login_attempts + 1;
                $data = ['failed_login_attempts' => $attempts];
                $lockMsg = '';
                if ($attempts >= 5) {
                    $data['locked_until'] = now()->addMinutes(30);
                    $lockMsg = ' Account locked for 30 minutes.';
                } else {
                    $lockMsg = ' ' . (5 - $attempts) . ' attempt(s) remaining.';
                }
                $user->update($data);
            }

            AuditLog::log('employee_login_failed', null, [], [
                'identifier' => $identifier,
                'ip'         => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'login' => 'Invalid credentials.' . ($lockMsg ?? ''),
            ]);
        }

        $user = Auth::user();

        if (!$user->canAccessEmployeePortal()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'Employee portal access is not enabled.',
            ]);
        }

        if (!$user->employee) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'No employee profile linked. Contact HR.',
            ]);
        }

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
            'last_login_at'         => now(),
            'last_login_ip'         => $request->ip(),
            'login_count'           => $user->login_count + 1,
        ]);

        RateLimiter::clear($this->throttleKey($request));
        AuditLog::log('employee_login_success');
        $request->session()->regenerate();

        return redirect()->intended(route('employee.dashboard'))
            ->with('success', 'Welcome back, ' . $user->employee->first_name . '!');
    }

    public function logout(Request $request)
    {
        AuditLog::log('employee_logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employee.login')
            ->with('info', 'You have been signed out.');
    }

    protected function checkRateLimit(Request $request): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 10)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));
            throw ValidationException::withMessages([
                'login' => "Too many login attempts. Please wait {$seconds} seconds.",
            ]);
        }
    }

    protected function throttleKey(Request $request): string
    {
        return 'employee_login_' . strtolower($request->login) . '|' . $request->ip();
    }
}