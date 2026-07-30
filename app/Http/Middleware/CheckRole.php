<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (Auth::check()) {
            $userRole = User::normalizeRole(Auth::user()->role);
            $allowed = array_map([User::class, 'normalizeRole'], $roles);

            if ($userRole && in_array($userRole, $allowed, true)) {
            return $next($request);
            }
        }

        return redirect()->route('auth.login')->with('error', 'You do not have access.');
    }
}