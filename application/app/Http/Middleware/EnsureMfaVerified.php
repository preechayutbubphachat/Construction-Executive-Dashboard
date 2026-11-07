<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMfaVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasMfaEnabled()) {
            if ($request->session()->get('mfa_verified') !== true) {
                if (! $request->routeIs('mfa.*') && ! $request->routeIs('logout')) {
                    $request->session()->put('url.intended', $request->fullUrl());

                    return redirect()->route('mfa.challenge');
                }
            }
        }

        return $next($request);
    }
}
