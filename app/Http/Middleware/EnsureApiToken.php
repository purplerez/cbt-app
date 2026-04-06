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
        // Only create token once per session, not on every request
        if (auth()->check() && !$request->session()->has('api_token')) {
            try {
                // Check if user already has an active token to avoid duplicate creation
                $user = auth()->user();
                if ($user->tokens()->exists()) {
                    // Use existing token instead of creating new one
                    $existingToken = $user->tokens()->first();
                    $request->session()->put('api_token', $existingToken->plainTextToken ?? null);
                } else {
                    // Only create if no token exists
                    $token = $user->createToken('dashboard-token')->plainTextToken;
                    $request->session()->put('api_token', $token);
                }
            } catch (\Exception $e) {
                // Silently fail - API token is optional for web
                \Log::error('EnsureApiToken failed: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}
