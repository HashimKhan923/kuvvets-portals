<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackLastActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            cache()->put(
                'user_last_active_' . Auth::id(),
                now(),
                now()->addMinutes(15)
            );
        }
        return $next($request);
    }
}