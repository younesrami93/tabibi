<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Check if user is logged in (just in case)
        if (!Auth::check()) {
            return redirect('login');
        }

        // 2. Get current user
        $user = Auth::user();

        // 3. Check if the user's role is in the list of allowed roles
        // Example usage: middleware('role:super_admin,doctor')
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // 4. If not allowed, abort with 403 (Forbidden) or redirect
        abort(403, 'Unauthorized action.');
    }
}