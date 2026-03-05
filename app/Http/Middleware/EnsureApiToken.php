<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiToken
{
    /**
     * Ensure the authenticated user always has an api_token in their web session.
     * This handles existing sessions that predate the token-on-login logic,
     * so users don't need to re-login to get a functioning dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !$request->session()->has('api_token')) {
            $token = auth()->user()->createToken('dashboard-token')->plainTextToken;
            $request->session()->put('api_token', $token);
        }

        return $next($request);
    }
}
