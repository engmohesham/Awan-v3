<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Disable CSRF for API routes
        if ($request->is('api/*')) {
            $request->attributes->set('skip-csrf', true);
        }

        return $next($request);
    }
}
