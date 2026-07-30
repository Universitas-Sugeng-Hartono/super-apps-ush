<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Auth;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::user() && User::normalizeRole(Auth::user()->role) === 'superadmin') {
             return $next($request);
        }

        return redirect()->route('auth.login')->with('error','You have not admin access');
      // It will redirect user back to home screen if they do not have is_admin=1 assigned in database
    }
}