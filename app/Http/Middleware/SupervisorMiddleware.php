<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SupervisorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isSupervisor()) {
            return redirect('/dashboard')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
