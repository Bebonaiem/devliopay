<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->two_factor_enabled && ! $request->session()->has('2fa_verified_at')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Two-factor authentication required.'], 403);
            }

            $request->session()->put('2fa_intended_url', $request->url());

            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
