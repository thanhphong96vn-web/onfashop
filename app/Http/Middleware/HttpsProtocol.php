<?php

namespace App\Http\Middleware;

use Closure;

class HttpsProtocol {
    public function handle($request, Closure $next)
    {
        // Skip HTTPS for local
        if (env('APP_ENV') === 'local') {
            return $next($request);
        }
        
        if (env('FORCE_HTTPS') == "On" && !$request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }
        return $next($request);
    }
}