<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    // ── Show admin login form ─────────────────────────────
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    // ── Handle admin login ────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $this->checkRateLimit($request);

        // Support email OR username login
        $field = filter_var($request->email, FILTER_VALIDATE_EMAIL)
            ? 'email' : 'username';

        $user = User::where($field, $request->email)->first();

        // Pre-auth checks
        if ($user) {
            if (!$user->is_active) {
                throw ValidationException::withMessages([
                    'email' => 'This account has been deactivated. Please contact HR.',
                ]);
            }

            if ($user->isLocked()) {
                throw ValidationException::withMessages([
                    'email' => 'Account is temporarily locked until '
                        . $user->locked_until->format('h:i A')
                        . '. Try again later.',
                ]);
            }

            if (!$user->isAdmin()) {
                AuditLog::log('admin_login_denied_not_admin', null, [], [
                    'email' => $request->email,
                    'ip'    => $request->ip(),
                ]);
                throw ValidationException::withMessages([
                    'email' => 'This account does not have admin portal access.',
                ]);
            }
        }

        // Attempt login
        if (!Auth::attempt(
            [$field => $request->email, 'password' => $request->password],
            $request->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey($request), 300);

            if ($user) {
                $attempts = $user->failed_login_attempts + 1;
                $data     = ['failed_login_attempts' => $attempts];

                if ($attempts >= 5) {
                    $data['locked_until'] = now()->addMinutes(30);
                    $lockMsg = ' Account locked for 30 minutes.';
                } else {
                    $lockMsg = ' ' . (5 - $attempts) . ' attempt(s) remaining.';
                }

                $user->update($data);
            }

            AuditLog::log('admin_login_failed', null, [], [
                'email' => $request->email, 'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.' . ($lockMsg ?? ''),
            ]);
        }

        $user = Auth::user();

        // Final check: must be admin
        if (!$user->canAccessAdminPortal()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Admin portal access is not enabled for this account.',
            ]);
        }

        // Successful login
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
            'last_login_at'         => now(),
            'last_login_ip'         => $request->ip(),
            'login_count'           => $user->login_count + 1,
        ]);

        RateLimiter::clear($this->throttleKey($request));
        AuditLog::log('admin_login_success');

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    // ── Logout ────────────────────────────────────────────
    public function logout(Request $request)
    {
        AuditLog::log('admin_logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('info', 'You have been signed out successfully.');
    }

    // ── Helpers ───────────────────────────────────────────
    protected function checkRateLimit(Request $request): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 10)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please wait {$seconds} seconds.",
            ]);
        }
    }

    protected function throttleKey(Request $request): string
    {
        return 'admin_login_' . strtolower($request->email) . '|' . $request->ip();
    }
}