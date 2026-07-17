<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetAppUrlByRequest
{
    public function handle(Request $request, Closure $next)
    {
        $scheme = $request->getScheme();
        $host = $request->getHost();

        config(['app.url' => $scheme . '://' . $host]);

        return $next($request);
    }
}
